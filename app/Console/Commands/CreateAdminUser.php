<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create';

    protected $description = 'Create the administrator account using hidden password prompts';

    public function handle(): int
    {
        $name = trim((string) $this->ask('Nombre del administrador'));
        $email = mb_strtolower(trim((string) $this->ask('Correo del administrador')));

        $validator = Validator::make(
            ['name' => $name, 'email' => $email],
            ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255']],
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->error('Ya existe una cuenta con ese correo. No se modificó ningún usuario.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Contraseña (mínimo 12 caracteres)');
        $passwordConfirmation = (string) $this->secret('Confirma la contraseña');

        if (mb_strlen($password) < 12 || $password !== $passwordConfirmation) {
            $this->error('La contraseña debe tener al menos 12 caracteres y ambas entradas deben coincidir.');

            return self::FAILURE;
        }

        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->email_verified_at = now();
        $user->is_admin = true;
        $user->save();

        $this->info('La cuenta administradora se creó correctamente.');

        return self::SUCCESS;
    }
}