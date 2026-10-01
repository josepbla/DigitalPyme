<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0B1220">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DigitalPyme')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: dark;
            --color-background: #0B1220;
            --color-surface: #101a2c;
            --color-surface-raised: #152238;
            --color-accent: #8ECAE6;
            --color-accent-strong: #cfe9fa;
            --color-text: #F5F7FA;
            --color-muted: #A9B4C4;
            --color-border: #34435a;
            --font-display: 'DM Serif Display', Georgia, serif;
            --font-body: 'DM Sans', sans-serif;
        }

        * { box-sizing: border-box; }
        .visually-hidden { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
        body {
            min-width: 320px;
            min-height: 100vh;
            margin: 0;
            color: var(--color-text);
            background-color: var(--color-background);
            background-image: linear-gradient(135deg, rgb(142 202 230 / 4%) 1px, transparent 1px),
                linear-gradient(45deg, rgb(142 202 230 / 3%) 1px, transparent 1px);
            background-size: 44px 44px;
            font-family: var(--font-body);
            font-size: 1rem;
            line-height: 1.6;
        }
        a { color: var(--color-accent-strong); }
        a:focus-visible, summary:focus-visible, input:focus-visible, textarea:focus-visible, button:focus-visible {
            outline: 3px solid var(--color-accent);
            outline-offset: 3px;
        }
        .site-header { position: relative; z-index: 10; border-bottom: 1px solid var(--color-border); background: rgb(11 18 32 / 96%); }
        .header-main { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: .7rem; width: min(100% - 2rem, 1540px); margin: 0 auto; padding: .7rem 0; }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: .65rem;
            min-width: 0;
            color: var(--color-text);
            font-size: 1.18rem;
            font-weight: 700;
            text-decoration: none;
        }
        .brand-mark { display: grid; width: 2.35rem; height: 2.35rem; flex: 0 0 auto; place-items: center; border: 1px solid var(--color-accent); border-radius: 5px; color: var(--color-background); background: var(--color-accent); font-size: .8rem; font-weight: 800; }
        .category-menu, .nav-dropdown { position: relative; }
        .category-menu { grid-column: 1 / -1; grid-row: 2; }
        .category-trigger { display: inline-flex; min-height: 42px; align-items: center; gap: .55rem; padding: .45rem .8rem; border: 1px solid var(--color-accent); border-radius: 5px; color: var(--color-background); background: var(--color-accent); font-size: .86rem; font-weight: 700; cursor: pointer; }
        .category-trigger svg, .header-link svg, .header-locator svg { width: 1.2rem; height: 1.2rem; flex: 0 0 auto; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.8; }
        .category-menu[open] .category-trigger { background: var(--color-accent-strong); }
        .primary-nav { min-width: 0; overflow-x: auto; }
        .nav-list { display: flex; align-items: center; justify-content: flex-start; gap: .25rem; width: max-content; min-width: 100%; margin: 0; padding: .2rem 0; flex-wrap: nowrap; list-style: none; }
        .nav-link { display: inline-flex; min-height: 40px; align-items: center; padding: .45rem .7rem; border-radius: 4px; color: var(--color-muted); font-size: .82rem; text-decoration: none; white-space: nowrap; }
        .nav-link:hover, .nav-link:focus-visible { color: var(--color-text); background: var(--color-surface-raised); text-decoration: none; }
        .nav-dropdown summary { display: flex; min-height: 40px; align-items: center; gap: .5rem; padding: .45rem .7rem; border-radius: 4px; color: var(--color-muted); font-size: .82rem; list-style: none; cursor: pointer; }
        .nav-dropdown summary::-webkit-details-marker { display: none; }
        .nav-dropdown summary::after { width: .42rem; height: .42rem; transform: translateY(-.12rem) rotate(45deg); border-right: 1px solid currentColor; border-bottom: 1px solid currentColor; content: ''; }
        .nav-dropdown[open] summary::after { transform: translateY(.1rem) rotate(225deg); }
        .nav-dropdown summary:hover { color: var(--color-text); background: var(--color-surface-raised); }
        .dropdown-menu { position: absolute; z-index: 15; top: calc(100% + .45rem); left: 0; display: grid; min-width: min(80vw, 280px); gap: .2rem; margin: 0; padding: .55rem; border: 1px solid var(--color-border); border-radius: 5px; background: var(--color-surface); box-shadow: 0 16px 34px rgb(0 0 0 / 30%); list-style: none; }
        .dropdown-menu a { display: block; padding: .6rem .65rem; border-radius: 3px; color: var(--color-muted); font-size: .86rem; text-decoration: none; }
        .dropdown-menu a:hover, .dropdown-menu a:focus-visible { color: var(--color-text); background: var(--color-surface-raised); }
        .category-menu .dropdown-menu { left: 0; }
        .header-subnav { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: .5rem 1rem; min-height: 48px; padding: .25rem max(1rem, calc((100% - 1540px) / 2)); border-top: 1px solid rgb(52 67 90 / 70%); background: rgb(8 14 25 / 74%); }
        .header-locator { display: inline-flex; align-items: center; gap: .45rem; min-width: 0; color: var(--color-muted); font-size: .75rem; white-space: nowrap; }
        .header-locator svg { color: var(--color-accent); }
        .header-actions { display: flex; grid-column: 2; grid-row: 1; align-items: center; justify-content: flex-end; gap: .35rem; }
        .header-link { display: inline-flex; min-height: 42px; align-items: center; justify-content: center; gap: .45rem; padding: .4rem .55rem; border: 1px solid transparent; border-radius: 5px; color: var(--color-muted); font-size: .78rem; text-decoration: none; white-space: nowrap; }
        .header-link:hover { border-color: var(--color-border); color: var(--color-text); background: var(--color-surface); }
        .header-link .header-link-label { display: none; }
        .nav-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: .45rem .9rem;
            border: 1px solid var(--color-accent);
            border-radius: 5px;
            color: var(--color-background);
            background: var(--color-accent);
            font-size: .88rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }
        .nav-cta:hover { background: var(--color-accent-strong); }
        .site-search { position: relative; display: flex; grid-column: 1 / -1; grid-row: 3; min-width: 0; align-items: center; }
        .site-search input { width: 100%; min-height: 44px; padding: .55rem 2.9rem .55rem .9rem; border: 1px solid var(--color-border); border-radius: 6px; color: var(--color-text); background: var(--color-surface); font: inherit; font-size: .88rem; }
        .site-search input:focus { border-color: var(--color-accent); outline-offset: 1px; }
        .search-icon { position: absolute; right: .85rem; display: grid; width: 1.2rem; height: 1.2rem; place-items: center; color: var(--color-muted); pointer-events: none; }
        .search-icon svg, .cart-icon svg { width: 100%; height: 100%; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.8; }
        .cart-trigger { position: relative; display: inline-flex; min-height: 42px; align-items: center; justify-content: center; gap: .45rem; padding: .4rem .55rem; border: 1px solid var(--color-border); border-radius: 5px; color: var(--color-text); background: var(--color-surface); font: inherit; font-size: .78rem; cursor: pointer; }
        .cart-icon { display: grid; width: 1.2rem; height: 1.2rem; place-items: center; }
        .cart-count { display: grid; min-width: 1.25rem; height: 1.25rem; place-items: center; padding-inline: .25rem; border-radius: 999px; color: var(--color-background); background: var(--color-accent); font-size: .72rem; font-weight: 700; }
        .service-card[hidden] { display: none; }
        .catalog-status { min-height: 1.5rem; margin-top: 1rem; color: var(--color-muted); }
        .add-to-cart { display: inline-flex; min-height: 44px; align-items: center; justify-content: center; gap: .5rem; margin-top: auto; padding: .55rem .75rem; border: 1px solid var(--color-accent); border-radius: 4px; color: var(--color-background); background: var(--color-accent); font: inherit; font-size: .82rem; font-weight: 700; cursor: pointer; }
        .add-to-cart:hover:not(:disabled) { background: var(--color-accent-strong); }
        .add-to-cart:disabled { opacity: .5; cursor: not-allowed; }
        main { width: min(100% - 2rem, 760px); margin: 0 auto; padding: 2.75rem 0 5rem; }
        main.home-main { width: 100%; max-width: none; padding-top: 0; }
        html { scroll-behavior: smooth; scroll-padding-top: 2rem; }
        .eyebrow {
            margin: 0 0 .75rem;
            color: var(--color-accent);
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: 0;
            text-transform: uppercase;
        }
        h1, h2 { font-family: var(--font-display); font-weight: 400; line-height: 1.15; }
        h1 { max-width: 15ch; margin: 0; font-size: 2.5rem; }
        .intro { max-width: 58ch; margin: 1rem 0 2rem; color: var(--color-muted); }
        .form-panel {
            padding: clamp(1.25rem, 4vw, 2.25rem);
            border: 1px solid var(--color-border);
            border-radius: 8px;
            background: var(--color-surface);
        }
        .privacy-toc { margin: 2rem 0; padding: 1.25rem 1.5rem; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-surface); }
        .privacy-toc h2 { margin: 0 0 .75rem; font-size: 1.2rem; }
        .privacy-toc ol { display: grid; grid-template-columns: 1fr; gap: .45rem 1.5rem; margin: 0; padding-left: 1.25rem; color: var(--color-accent); }
        .privacy-toc a { color: var(--color-muted); text-underline-offset: 3px; }
        .privacy-content { max-width: 72ch; }
        .privacy-section { padding: 1.5rem 0; border-bottom: 1px solid var(--color-border); scroll-margin-top: 1.5rem; }
        .privacy-section h2 { margin: 0 0 .65rem; font-size: 1.55rem; }
        .privacy-section p { margin: .6rem 0 0; color: var(--color-muted); }
        .privacy-section a { color: var(--color-accent-strong); text-underline-offset: 3px; }
        .form-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
        .field { display: grid; gap: .45rem; }
        .field-full { grid-column: auto; }
        label, legend { color: var(--color-text); font-size: .92rem; font-weight: 600; }
        input:not([type="checkbox"]), textarea {
            width: 100%;
            min-height: 48px;
            padding: .7rem .8rem;
            border: 1px solid var(--color-border);
            border-radius: 5px;
            color: var(--color-text);
            background: var(--color-background);
            font: inherit;
        }
        textarea { min-height: 132px; resize: vertical; }
        input::placeholder, textarea::placeholder { color: #8794a8; }
        .field-hint, .field-error { margin: 0; font-size: .84rem; }
        .field-hint { color: var(--color-muted); }
        .field-error { color: #ffc1c1; }
        fieldset { min-width: 0; margin: 0; padding: 0; border: 0; }
        .service-list { display: grid; gap: .65rem; margin-top: .7rem; }
        .service-option {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            padding: .8rem .9rem;
            border: 1px solid var(--color-border);
            border-radius: 5px;
            background: var(--color-background);
            cursor: pointer;
        }
        .service-option input, .privacy-option input { width: 1.15rem; height: 1.15rem; margin: .2rem 0 0; accent-color: var(--color-accent); }
        .service-option span { display: grid; gap: .15rem; }
        .service-option small { color: var(--color-muted); font-size: .84rem; }
        .privacy-option { display: flex; align-items: flex-start; gap: .7rem; color: var(--color-muted); }
        .submit-button {
            min-height: 50px;
            padding: .75rem 1.2rem;
            border: 1px solid var(--color-accent);
            border-radius: 5px;
            color: var(--color-background);
            background: var(--color-accent);
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }
        .submit-button:hover { background: var(--color-accent-strong); }
        .notice { margin-bottom: 1.25rem; padding: .85rem 1rem; border-radius: 5px; }
        .notice-success { border: 1px solid var(--color-accent); color: var(--color-accent-strong); background: #13293a; }
        .site-footer { border-top: 1px solid var(--color-border); color: var(--color-muted); background: #080e19; font-size: .9rem; }
        .footer-grid { display: grid; grid-template-columns: 1fr; gap: 2rem; width: min(100% - 2rem, 1200px); margin: 0 auto; padding: 2.5rem 0; }
        .footer-brand { color: var(--color-text); font-family: var(--font-display); font-size: 1.6rem; text-decoration: none; }
        .footer-summary { max-width: 38ch; margin: .85rem 0 1.25rem; }
        .footer-heading { margin: .15rem 0 1rem; color: var(--color-text); font-size: 1rem; }
        .footer-links { display: grid; gap: .55rem; margin: 0; padding: 0; list-style: none; }
        .footer-links a { color: var(--color-muted); text-decoration-color: transparent; text-underline-offset: 4px; }
        .footer-links a:hover, .footer-links a:focus-visible { color: var(--color-accent-strong); text-decoration-color: currentColor; }
        .footer-bottom { display: flex; flex-direction: column; align-items: flex-start; gap: 1rem; width: min(100% - 2rem, 1200px); margin: 0 auto; padding: 1.1rem 0; border-top: 1px solid var(--color-border); font-size: .82rem; }
        .footer-bottom p { margin: 0; }
        .footer-bottom a { color: var(--color-muted); text-underline-offset: 4px; }
        .footer-watermark { height: 3.5rem; overflow: hidden; color: rgb(142 202 230 / 12%); font-family: var(--font-body); font-size: 3rem; font-weight: 700; line-height: .95; text-align: center; white-space: nowrap; }
        .home-hero { position: relative; isolation: isolate; display: grid; min-height: 540px; align-content: center; padding: 4rem 1rem; overflow: hidden; background: var(--color-surface); }
        .home-hero::before { position: absolute; z-index: -1; inset: 0; background: rgb(11 18 32 / 76%); content: ''; }
        .home-hero-image { position: absolute; z-index: -2; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: center 54%; }
        .home-hero-content { max-width: 680px; }
        .home-hero h1 { max-width: 12ch; }
        .home-hero .intro { max-width: 52ch; margin: 1.25rem 0 1.75rem; font-size: 1.1rem; }
        .hero-actions { display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap; }
        .hero-secondary { display: inline-flex; align-items: center; gap: .45rem; min-height: 44px; color: var(--color-accent-strong); font-weight: 600; text-underline-offset: 5px; }
        .section-band { padding: clamp(3.5rem, 8vw, 6.5rem) max(1rem, calc((100vw - 1200px) / 2)); border-top: 1px solid var(--color-border); }
        .section-heading { max-width: 680px; margin-bottom: 2rem; }
        .section-heading h2 { margin: 0; font-size: 2rem; }
        .section-heading p:last-child { color: var(--color-muted); }
        .service-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
        .service-card, .trust-item, .quote-card {
            padding: 1.25rem;
            border: 1px solid var(--color-border);
            border-radius: 6px;
            background: var(--color-surface);
        }
        .service-card { display: flex; min-height: 100%; flex-direction: column; align-items: flex-start; }
        .service-icon { width: 2.5rem; height: 2.5rem; margin-bottom: 1rem; color: var(--color-accent); }
        .service-icon svg { width: 100%; height: 100%; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.5; }
        .service-card h3 { margin: 0 0 .6rem; font-size: 1.1rem; }
        .service-card p, .trust-item p, .quote-card p { margin: 0; color: var(--color-muted); }
        .service-action { display: inline-flex; align-items: center; gap: .5rem; min-height: 44px; margin-top: auto; padding-top: 1rem; color: var(--color-accent-strong); font-size: .9rem; font-weight: 700; text-underline-offset: 4px; }
        .section-cta { display: inline-flex; margin-top: 1.5rem; }
        .quiet-note { max-width: 66ch; color: var(--color-muted); }
        .footer-callout { max-width: 35ch; }
        .footer-legal { display: grid; gap: .7rem; }
        .footer-legal details { color: var(--color-muted); }
        .footer-legal summary { color: var(--color-text); cursor: pointer; }
        .footer-legal p { max-width: 34ch; font-size: .82rem; }
        .hero-slide-message { min-height: 1.7em; transition: opacity .2s ease; }
        .hero-slide-controls { display: inline-flex; align-items: center; gap: .55rem; margin-top: 1.25rem; color: var(--color-muted); font-size: .78rem; }
        .hero-slide-controls button { display: inline-grid; width: 2rem; height: 2rem; place-items: center; border: 1px solid var(--color-border); border-radius: 50%; color: var(--color-accent-strong); background: rgb(11 18 32 / 76%); font: inherit; cursor: pointer; }
        .hero-slide-controls button:hover { border-color: var(--color-accent); background: var(--color-surface-raised); }
        .hero-slide-index { min-width: 3.5rem; text-align: center; }
        .checkout-dialog { width: min(100% - 1.5rem, 680px); max-height: min(92vh, 860px); margin: auto; padding: 0; overflow: auto; border: 1px solid var(--color-border); border-radius: 8px; color: var(--color-text); background: var(--color-background); box-shadow: 0 24px 80px rgb(0 0 0 / 55%); }
        .checkout-dialog::backdrop { background: rgb(3 8 17 / 78%); backdrop-filter: blur(4px); }
        .checkout-shell { padding: 1.1rem; }
        .checkout-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; }
        .checkout-header h2 { margin: 0; font-size: 1.6rem; }
        .checkout-close { display: grid; width: 2.5rem; height: 2.5rem; flex: 0 0 auto; place-items: center; border: 1px solid var(--color-border); border-radius: 50%; color: var(--color-text); background: transparent; font-size: 1.4rem; cursor: pointer; }
        .checkout-progress { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .3rem; margin: 1.4rem 0; padding: 0; list-style: none; }
        .checkout-progress li { display: grid; justify-items: center; gap: .4rem; color: var(--color-muted); font-size: .62rem; text-align: center; }
        .step-number { display: grid; width: 1.8rem; height: 1.8rem; place-items: center; border: 1px solid var(--color-border); border-radius: 50%; color: var(--color-muted); background: var(--color-surface); font-size: .75rem; font-weight: 700; }
        .checkout-progress li[aria-current="step"] { color: var(--color-accent-strong); }
        .checkout-progress li[aria-current="step"] .step-number { border-color: var(--color-accent); color: var(--color-background); background: var(--color-accent); }
        .checkout-step h3 { margin: 0 0 .45rem; font-size: 1.2rem; }
        .checkout-step > p { margin: 0 0 1rem; color: var(--color-muted); font-size: .9rem; }
        .checkout-items { display: grid; gap: .6rem; margin: 1rem 0; padding: 0; list-style: none; }
        .checkout-item { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .8rem; border: 1px solid var(--color-border); border-radius: 5px; background: var(--color-surface); }
        .checkout-item-info { display: grid; gap: .15rem; }
        .checkout-item-info small { color: var(--color-muted); }
        .remove-item { min-width: 2.5rem; min-height: 2.5rem; border: 1px solid var(--color-border); border-radius: 4px; color: var(--color-muted); background: transparent; font: inherit; cursor: pointer; }
        .checkout-total { display: flex; justify-content: space-between; gap: 1rem; padding: .85rem 0; border-top: 1px solid var(--color-border); color: var(--color-muted); font-size: .88rem; }
        .checkout-total strong { color: var(--color-text); }
        .checkout-step[hidden], [hidden].checkout-status { display: none; }
        .checkout-field { display: grid; gap: .4rem; margin: .85rem 0; }
        .checkout-field input { min-height: 46px; }
        .checkout-error { margin: .8rem 0; padding: .7rem .8rem; border: 1px solid #a84d58; border-radius: 4px; color: #ffd9dc; background: #351a21; font-size: .85rem; }
        .checkout-error[hidden] { display: none; }
        .checkout-actions { display: flex; justify-content: space-between; gap: .75rem; margin-top: 1.2rem; }
        .checkout-actions-end { justify-content: flex-end; }
        .button-secondary { min-height: 46px; padding: .6rem .9rem; border: 1px solid var(--color-border); border-radius: 4px; color: var(--color-text); background: transparent; font: inherit; font-size: .85rem; cursor: pointer; }
        .payment-notice { display: flex; gap: .75rem; margin: 1rem 0; padding: 1rem; border: 1px solid var(--color-accent); border-radius: 5px; color: var(--color-accent-strong); background: rgb(142 202 230 / 7%); }
        .payment-notice svg { width: 1.35rem; height: 1.35rem; flex: 0 0 auto; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.7; }
        .payment-notice p { margin: 0; font-size: .84rem; }
        .payment-brands { display: flex; align-items: center; gap: .6rem; margin: 1rem 0; }
        .payment-brand { display: inline-grid; min-width: 4.4rem; min-height: 2.4rem; place-items: center; padding: .35rem .5rem; border: 1px solid var(--color-border); border-radius: 4px; background: var(--color-surface); font-size: .85rem; font-style: italic; font-weight: 700; }
        .payment-brand-visa { color: #dbe6ff; }
        .payment-brand-mastercard { color: var(--color-text); font-size: .7rem; font-style: normal; }
        .checkout-success-mark { display: grid; width: 3.5rem; height: 3.5rem; place-items: center; margin: 1rem 0; border: 1px solid var(--color-accent); border-radius: 50%; color: var(--color-background); background: var(--color-accent); font-size: 1.5rem; }
        .checkout-success-copy { color: var(--color-muted); }
        .admin-main { width: min(100% - 2rem, 1320px); padding-top: 2rem; }
        .admin-list-heading { display: flex; flex-direction: column; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
        .admin-list-heading h1 { max-width: none; font-size: 2.5rem; }
        .admin-list-heading .intro { margin: .45rem 0 0; }
        .admin-kpis { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; margin: 1.5rem 0 2.5rem; }
        .admin-kpi { display: grid; min-height: 110px; align-content: space-between; gap: .75rem; padding: 1rem; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-surface); }
        .admin-kpi span { color: var(--color-muted); font-size: .8rem; }
        .admin-kpi strong { color: var(--color-accent-strong); font-family: var(--font-display); font-size: 2rem; font-weight: 400; line-height: 1; }
        .admin-activity-heading { display: flex; flex-direction: column; align-items: flex-start; justify-content: space-between; gap: .75rem; margin-bottom: 1rem; }
        .admin-activity-heading .eyebrow { margin-bottom: .35rem; }
        .admin-activity-heading h2 { margin: 0; font-size: 1.65rem; }
        .admin-status-form { display: flex; align-items: center; gap: .4rem; }
        .admin-status-form select { min-width: 8.5rem; min-height: 38px; padding: .35rem; border: 1px solid var(--color-border); border-radius: 4px; color: var(--color-text); background: var(--color-background); font: inherit; font-size: .78rem; }
        .admin-status-save { min-height: 38px; padding: .35rem .55rem; border: 1px solid var(--color-accent); border-radius: 4px; color: var(--color-background); background: var(--color-accent); font: inherit; font-size: .75rem; font-weight: 700; cursor: pointer; }
        .admin-status-save:hover { background: var(--color-accent-strong); }
        .admin-dashboard-table { min-width: 850px; }
        .admin-request-filters { display: flex; flex-direction: column; align-items: stretch; gap: .75rem; margin: 1.5rem 0; padding: 1rem; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-surface); }
        .admin-search-field, .admin-status-field { display: grid; flex: 1 1 240px; gap: .35rem; }
        .admin-search-field span, .admin-status-field span { color: var(--color-muted); font-size: .78rem; font-weight: 600; }
        .admin-request-filters input, .admin-request-filters select { width: 100%; min-height: 44px; padding: .55rem .7rem; border: 1px solid var(--color-border); border-radius: 4px; color: var(--color-text); background: var(--color-background); font: inherit; }
        .admin-request-filters .submit-button { min-height: 44px; }
        .admin-clear-filter { min-height: 44px; display: inline-flex; align-items: center; padding-inline: .55rem; font-size: .82rem; }
        .admin-table-wrap { width: 100%; overflow-x: auto; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-surface); }
        .admin-requests-table { width: 100%; border-collapse: collapse; text-align: left; }
        .admin-requests-table th, .admin-requests-table td { padding: .85rem; border-bottom: 1px solid var(--color-border); vertical-align: top; }
        .admin-requests-table th { color: var(--color-muted); background: var(--color-surface-raised); font-size: .76rem; font-weight: 700; white-space: nowrap; }
        .admin-requests-table td { font-size: .84rem; }
        .admin-client-email, .admin-client-meta { display: block; margin-top: .2rem; overflow-wrap: anywhere; color: var(--color-muted); font-size: .78rem; }
        .admin-service-list { display: grid; gap: .25rem; margin: 0; padding-left: 1rem; }
        .admin-message summary { color: var(--color-accent-strong); cursor: pointer; }
        .admin-message p { max-width: 34ch; color: var(--color-muted); white-space: pre-wrap; }
        .admin-status-badge { display: inline-flex; padding: .25rem .55rem; border: 1px solid var(--color-border); border-radius: 99px; color: var(--color-text); background: var(--color-surface-raised); font-size: .72rem; white-space: nowrap; }
        .admin-status-new { border-color: var(--color-accent); color: var(--color-accent-strong); }
        .admin-empty-state { padding: 2rem; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-surface); }
        .admin-empty-state h2 { margin: 0; font-size: 1.3rem; }
        .admin-empty-state p { margin: .5rem 0 0; color: var(--color-muted); }
        .admin-social-composer { margin: 1.5rem 0; padding: 1.25rem 0 1.5rem; border-block: 1px solid var(--color-border); }
        .admin-social-composer h2, .admin-social-list-heading h2 { margin: 0; font-size: 1.45rem; }
        .admin-social-composer > p, .admin-social-list-heading p { margin: .4rem 0 1rem; color: var(--color-muted); font-size: .86rem; }
        .admin-social-form-grid { display: grid; grid-template-columns: 1fr; gap: .85rem; }
        .admin-social-field { display: grid; gap: .35rem; min-width: 0; }
        .admin-social-field > span { color: var(--color-muted); font-size: .78rem; font-weight: 600; }
        .admin-social-field input, .admin-social-field select, .admin-social-field textarea { width: 100%; min-height: 44px; padding: .55rem .7rem; border: 1px solid var(--color-border); border-radius: 4px; color: var(--color-text); background: var(--color-background); font: inherit; font-size: .86rem; }
        .admin-social-field textarea { min-height: 110px; resize: vertical; }
        .admin-social-field input[type="file"] { min-height: auto; padding: .55rem; }
        .admin-social-form-actions { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; margin-top: 1rem; }
        .admin-social-form-actions .submit-button { min-height: 42px; }
        .admin-social-error { margin: 1rem 0; padding: .85rem 1rem; border: 1px solid #a84d58; border-radius: 5px; color: #ffd9dc; background: #351a21; }
        .admin-social-error ul { margin: .25rem 0 0; padding-left: 1.2rem; }
        .admin-social-list-heading { display: flex; flex-direction: column; justify-content: space-between; gap: .5rem; margin: 2rem 0 1rem; }
        .admin-social-list { border-top: 1px solid var(--color-border); }
        .admin-social-post { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; padding: 1.1rem 0; border-bottom: 1px solid var(--color-border); }
        .admin-social-post-main { display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: start; gap: .75rem; }
        .admin-social-image { display: block; width: 76px; height: 76px; object-fit: cover; border: 1px solid var(--color-border); border-radius: 4px; background: var(--color-surface); }
        .admin-social-image-placeholder { display: grid; width: 76px; height: 76px; place-items: center; border: 1px solid var(--color-border); border-radius: 4px; color: var(--color-muted); background: var(--color-surface); font-size: .72rem; text-align: center; }
        .admin-social-meta { display: flex; align-items: center; gap: .45rem; flex-wrap: wrap; margin-bottom: .35rem; }
        .admin-social-platform { color: var(--color-accent-strong); font-size: .74rem; font-weight: 700; }
        .admin-social-client, .admin-social-schedule { display: block; margin-top: .2rem; color: var(--color-muted); font-size: .78rem; }
        .admin-social-caption { margin: .5rem 0 0; color: var(--color-text); font-size: .86rem; white-space: pre-wrap; overflow-wrap: anywhere; }
        .admin-social-tools { display: grid; gap: .75rem; }
        .admin-social-tools details { padding: .7rem; border: 1px solid var(--color-border); border-radius: 5px; background: var(--color-surface); }
        .admin-social-tools summary { color: var(--color-accent-strong); font-size: .8rem; font-weight: 700; cursor: pointer; }
        .admin-social-tools form { display: grid; gap: .55rem; margin-top: .7rem; }
        .admin-social-tools input, .admin-social-tools select, .admin-social-tools textarea { width: 100%; min-height: 38px; padding: .4rem .55rem; border: 1px solid var(--color-border); border-radius: 4px; color: var(--color-text); background: var(--color-background); font: inherit; font-size: .78rem; }
        .admin-social-tools textarea { min-height: 64px; }
        .admin-social-tools button { justify-self: start; }
        .admin-social-published-link { display: inline-block; margin-top: .5rem; font-size: .78rem; }
        .admin-pagination { display: flex; flex-direction: column; align-items: flex-start; gap: 1rem; margin-top: 1rem; color: var(--color-muted); font-size: .82rem; }
        .admin-pagination div { display: flex; gap: .5rem; }
        .admin-pagination a, .admin-pagination span[aria-disabled] { display: inline-flex; min-height: 40px; align-items: center; padding: .45rem .75rem; border: 1px solid var(--color-border); border-radius: 4px; color: var(--color-text); text-decoration: none; }
        .admin-pagination span[aria-disabled="true"] { opacity: .5; }
        .checkout-shell { padding: .9rem; }
        .checkout-progress li { font-size: .56rem; }
        .checkout-actions { align-items: stretch; }
        .checkout-actions .nav-cta, .checkout-actions .button-secondary { flex: 1; padding-inline: .45rem; font-size: .78rem; }
        @media (min-width: 601px) {
            h1 { font-size: 4rem; }
            .section-heading h2 { font-size: 3rem; }
            .form-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .field-full { grid-column: 1 / -1; }
            .privacy-toc ol { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .service-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .footer-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 2rem 1.5rem; }
            .footer-bottom { flex-direction: row; justify-content: space-between; }
            .footer-watermark { height: 6rem; font-size: 6rem; }
            .home-hero { min-height: min(690px, 78vh); padding: clamp(4rem, 8vw, 7rem) max(1rem, calc((100vw - 1200px) / 2)); }
            .admin-list-heading { flex-direction: row; align-items: flex-start; }
            .admin-request-filters { flex-direction: row; align-items: flex-end; }
            .admin-kpis { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; }
            .admin-kpi { min-height: 130px; padding: 1.25rem; }
            .admin-activity-heading { flex-direction: row; align-items: flex-end; }
            .admin-social-form-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .admin-social-field-wide { grid-column: 1 / -1; }
            .admin-social-list-heading { flex-direction: row; align-items: flex-end; }
            .admin-social-post { grid-template-columns: minmax(0, 1fr) minmax(260px, .65fr); align-items: start; }
            .admin-requests-table th, .admin-requests-table td { padding: 1rem; }
            .admin-pagination { flex-direction: row; align-items: center; justify-content: space-between; }
            .checkout-shell { padding: 1.5rem; }
            .checkout-progress li { font-size: .7rem; }
            .checkout-actions .nav-cta, .checkout-actions .button-secondary { flex: initial; padding-inline: .9rem; font-size: .85rem; }
        }
        @media (min-width: 601px) {
            .header-main { grid-template-columns: auto minmax(0, 1fr) auto; gap: .75rem 1rem; padding-block: .8rem; }
            .category-menu { grid-column: 1; grid-row: 2; }
            .site-search { grid-column: 2; grid-row: 2; }
            .header-actions { grid-column: 3; grid-row: 1 / 3; gap: .4rem; }
            .header-link { padding-inline: .65rem; }
            .header-subnav { grid-template-columns: auto minmax(0, 1fr); gap: 1rem; }
            .header-subnav .primary-nav { justify-self: end; }
            .header-subnav .nav-list { justify-content: flex-end; }
        }
        @media (min-width: 901px) {
            .header-main { grid-template-columns: auto auto minmax(180px, 1fr) auto; gap: 1rem; }
            .category-menu, .site-search, .header-actions { grid-column: auto; grid-row: auto; }
            .header-actions { gap: .35rem; }
            .header-link { min-height: 44px; padding-inline: .75rem; }
            .cart-trigger { min-height: 44px; padding-inline: .75rem; font-size: .84rem; }
            .header-link .header-link-label { display: none; }
            .nav-list { gap: .5rem; }
        }
        @media (min-width: 1200px) {
            .header-main { grid-template-columns: auto auto minmax(280px, 1fr) auto; gap: 1.15rem; }
            .header-link { gap: .55rem; padding-inline: .7rem; }
            .header-link .header-link-label { display: inline; }
            .service-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .footer-grid { grid-template-columns: 1.2fr repeat(4, minmax(0, 1fr)); gap: clamp(1.25rem, 3vw, 3rem); }
            .footer-watermark { height: 8rem; font-size: 8.5rem; }
        }
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } .hero-slide-message { transition: none; } }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-main">
            <a class="brand" href="{{ route('home') }}" aria-label="DigitalPyme, inicio">
                <span class="brand-mark" aria-hidden="true">DP</span>
                <span>DigitalPyme</span>
            </a>

            <details class="category-menu">
                <summary class="category-trigger">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h6v6H4zM14 5h6v6h-6zM4 15h6v5H4zM14 15h6v5h-6z"/></svg>
                    Categorías
                </summary>
                <ul class="dropdown-menu">
                    <li><a href="{{ route('home') }}#servicio-paginas-web">Páginas web</a></li>
                    <li><a href="{{ route('home') }}#servicio-tiendas-online">Tiendas online</a></li>
                    <li><a href="{{ route('home') }}#servicio-automatizacion">Automatización de procesos</a></li>
                    <li><a href="{{ route('home') }}#servicio-redes-sociales">Gestión de redes sociales</a></li>
                </ul>
            </details>

            <form class="site-search" role="search" action="{{ route('home') }}#servicios" method="GET" data-catalog-search-form>
                <label class="visually-hidden" for="service-search">Buscar servicios</label>
                <input id="service-search" name="q" type="search" value="{{ request('q') }}" placeholder="¿Qué servicio estás buscando?" autocomplete="off" data-catalog-search>
                <span class="search-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg></span>
            </form>

            <div class="header-actions">
                <a class="header-link" href="{{ route('login') }}" aria-label="Administración">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M4.5 20v-1.5a7.5 7.5 0 0 1 15 0V20z"/></svg>
                    <span class="header-link-label">Administración</span>
                </a>
                <a class="header-link" href="{{ route('diagnostics.create') }}" aria-label="Solicitar Diagnóstico">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4.5h10a2 2 0 0 1 2 2v13H5v-13a2 2 0 0 1 2-2Z"/><path d="M9 4.5V3h6v1.5M8.5 10h7m-7 4h7m-7 4h4"/></svg>
                    <span class="header-link-label">Solicitar Diagnóstico</span>
                </a>
                @if (request()->routeIs('home'))
                    <button class="cart-trigger" id="cart-open" type="button" aria-haspopup="dialog" aria-controls="checkout-dialog" aria-label="Abrir carrito, 0 servicios">
                        <span class="cart-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M2.5 3h2l2.2 12.4a2 2 0 0 0 2 1.6h9.2a2 2 0 0 0 1.9-1.4L22 8H5.2M9 21h.01M18 21h.01"/><circle cx="9" cy="21" r="1"/><circle cx="18" cy="21" r="1"/></svg></span>
                        <span class="header-link-label">Carrito</span>
                        <span class="cart-count" id="cart-count" aria-live="polite">0</span>
                    </button>
                @endif
            </div>
        </div>

        <div class="header-subnav">
            <span class="header-locator">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.3"/></svg>
                Soluciones digitales para pymes
            </span>
            <nav class="primary-nav" aria-label="Navegación principal">
                <ul class="nav-list">
                    <li><a class="nav-link" href="{{ route('home') }}#inicio">Inicio</a></li>
                    <li><a class="nav-link" href="{{ route('home') }}#servicios">Servicios</a></li>
                    <li><a class="nav-link" href="{{ route('home') }}#precios">Precios</a></li>
                    <li><a class="nav-link" href="{{ route('home') }}#casos">Casos de éxito</a></li>
                    <li><a class="nav-link" href="{{ route('home') }}#nosotros">Nosotros</a></li>
                    <li><a class="nav-link" href="{{ route('privacy-policy') }}">Ayuda</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main id="contenido" class="@yield('main_class')">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="footer-grid">
            <section id="nosotros" aria-labelledby="footer-brand-title">
                <h2 class="footer-heading" id="footer-brand-title"><a class="footer-brand" href="{{ route('home') }}">DigitalPyme</a></h2>
                <p class="footer-summary">Presencia digital para pequeñas y medianas empresas, con un equipo que te acompaña en cada etapa.</p>
            </section>

            <nav aria-label="Servicios">
                <h2 class="footer-heading">Servicios</h2>
                <ul class="footer-links">
                    <li><a href="{{ route('home') }}#servicio-paginas-web">Páginas Web</a></li>
                    <li><a href="{{ route('home') }}#servicio-tiendas-online">Tiendas Online</a></li>
                    <li><a href="{{ route('home') }}#servicio-automatizacion">Automatización de Procesos</a></li>
                    <li><a href="{{ route('home') }}#servicio-redes-sociales">Gestión de Redes Sociales</a></li>
                </ul>
            </nav>

            <section id="casos" aria-labelledby="footer-cases-title">
                <h2 class="footer-heading" id="footer-cases-title">Casos de Éxito</h2>
                <p class="footer-callout">Conversemos sobre los objetivos digitales de tu negocio y diseñemos el siguiente paso.</p>
                <a href="{{ route('diagnostics.create') }}">Cuéntanos tu proyecto</a>
            </section>

            <nav aria-label="Información legal">
                <h2 class="footer-heading">Información</h2>
                <ul class="footer-links">
                    <li><a href="{{ route('privacy-policy') }}">Política de Privacidad</a></li>
                    <li><a href="{{ route('home') }}#precios">Precios y cotización</a></li>
                </ul>
            </nav>

            <section id="contacto" aria-labelledby="footer-contact-title">
                <h2 class="footer-heading" id="footer-contact-title">Contacto directo</h2>
                <p class="footer-summary">Cuéntanos qué necesita tu empresa. Te responderemos a través de los datos que compartas.</p>
                <a class="nav-cta" href="{{ route('diagnostics.create') }}">Hablemos de tu proyecto</a>
            </section>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} DigitalPyme. Todos los derechos reservados.</p>
            <p><a href="{{ route('home') }}#inicio">Volver al inicio</a></p>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>