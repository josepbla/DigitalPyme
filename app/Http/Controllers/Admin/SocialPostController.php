<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\SocialPost;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SocialPostController extends Controller
{
    private const STATUSES = [
        'draft' => 'Borrador',
        'scheduled' => 'Programada',
        'published' => 'Publicada',
        'failed' => 'Con error',
    ];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'client_id' => ['nullable', 'integer', Rule::exists('clients', 'id')],
            'platform' => ['nullable', Rule::in(['facebook', 'instagram'])],
            'status' => ['nullable', Rule::in(array_keys(self::STATUSES))],
        ]);

        $posts = SocialPost::query()
            ->with('client:id,name,email')
            ->when($filters['client_id'] ?? null, fn ($query, int $clientId) => $query->where('client_id', $clientId))
            ->when($filters['platform'] ?? null, fn ($query, string $platform) => $query->where('platform', $platform))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByRaw("CASE WHEN status = 'scheduled' THEN 0 ELSE 1 END")
            ->orderBy('scheduled_for')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.social.index', [
            'posts' => $posts,
            'clients' => Client::query()->orderBy('name')->get(['id', 'name', 'email']),
            'filters' => $filters,
            'statusLabels' => self::STATUSES,
            'platformLabels' => ['facebook' => 'Facebook', 'instagram' => 'Instagram'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')],
            'platform' => ['required', Rule::in(['facebook', 'instagram'])],
            'caption' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'status' => ['required', Rule::in(['draft', 'scheduled'])],
            'scheduled_for' => ['nullable', 'date', 'required_if:status,scheduled', 'after:now'],
        ]);

        if ($validated['status'] === 'scheduled' && blank($validated['scheduled_for'] ?? null)) {
            return back()->withErrors(['scheduled_for' => 'Indica cuándo planeas publicar este contenido.'])->withInput();
        }

        $imagePath = $request->file('image')?->store('social-posts', 'local');

        try {
            SocialPost::query()->create([
                'client_id' => $validated['client_id'],
                'created_by' => $request->user()->id,
                'platform' => $validated['platform'],
                'caption' => $validated['caption'],
                'image_path' => $imagePath,
                'status' => $validated['status'],
                'scheduled_for' => $validated['status'] === 'scheduled' ? $validated['scheduled_for'] : null,
            ]);
        } catch (Throwable $exception) {
            if ($imagePath) {
                Storage::disk('local')->delete($imagePath);
            }

            throw $exception;
        }

        return redirect()->route('admin.social.index')->with('status', 'La publicación quedó guardada en tu agenda.');
    }

    public function updatePublication(Request $request, SocialPost $socialPost): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['draft', 'scheduled', 'published', 'failed'])],
            'scheduled_for' => ['nullable', 'date', 'required_if:status,scheduled', 'after:now'],
            // Published links are rendered as clickable anchors in the admin.
            // Restrict schemes so a stored javascript: URL cannot become an XSS vector.
            'published_url' => ['nullable', 'url:http,https', 'max:2048'],
            'published_at' => ['nullable', 'date'],
            'error_message' => ['nullable', 'string', 'max:5000', 'required_if:status,failed'],
        ]);

        if ($validated['status'] === 'scheduled' && blank($validated['scheduled_for'] ?? null)) {
            return back()->withErrors(['scheduled_for' => 'Indica una fecha futura para reprogramar.'])->withInput();
        }

        $socialPost->update([
            'status' => $validated['status'],
            'scheduled_for' => $validated['status'] === 'scheduled' ? $validated['scheduled_for'] : $socialPost->scheduled_for,
            'published_url' => $validated['status'] === 'published' ? ($validated['published_url'] ?? null) : null,
            'published_at' => $validated['status'] === 'published'
                ? ($validated['published_at'] ?? $socialPost->published_at ?? now())
                : null,
            'error_message' => $validated['status'] === 'failed' ? $validated['error_message'] : null,
        ]);

        return redirect()->route('admin.social.index')->with('status', 'Se actualizó el estado de la publicación.');
    }

    public function updateMetrics(Request $request, SocialPost $socialPost): RedirectResponse
    {
        abort_unless($socialPost->status === 'published', 422, 'Solo las publicaciones enviadas admiten métricas.');

        $validated = $request->validate([
            'reach' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'likes' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'comments' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);

        $socialPost->update($validated + ['metrics_updated_at' => now()]);

        return redirect()->route('admin.social.index')->with('status', 'Se actualizaron las métricas.');
    }

    public function image(SocialPost $socialPost): StreamedResponse
    {
        abort_unless($socialPost->image_path && Storage::disk('local')->exists($socialPost->image_path), 404);

        $stream = Storage::disk('local')->readStream($socialPost->image_path);
        abort_unless(is_resource($stream), 404);
        $contentType = match (strtolower(pathinfo($socialPost->image_path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
