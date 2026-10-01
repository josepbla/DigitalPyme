(() => {
    const dialog = document.querySelector('#checkout-dialog');

    if (!dialog) return;

    const form = document.querySelector('#checkout-form');
    const cartList = dialog.querySelector('[data-cart-items]');
    const cartEmpty = dialog.querySelector('[data-cart-empty]');
    const cartCount = document.querySelector('#cart-count');
    const cartTrigger = document.querySelector('#cart-open');
    const errorBox = dialog.querySelector('#checkout-error');
    const submitButton = dialog.querySelector('[data-checkout-submit]');
    const storageKey = 'digitalpyme:selected-services';
    const serviceCatalog = new Map();
    const serviceButtons = [...document.querySelectorAll('[data-add-to-cart]')];

    serviceButtons.forEach((button) => {
        const serviceId = button.dataset.serviceId;

        if (serviceId) {
            serviceCatalog.set(serviceId, {
                name: button.dataset.serviceName,
                price: Number(button.dataset.servicePrice) || null,
            });
        }
    });

    let cart = new Set();

    try {
        const savedServices = JSON.parse(localStorage.getItem(storageKey) || '[]');
        cart = new Set(savedServices.filter((serviceId) => serviceCatalog.has(String(serviceId))).map(String));
    } catch {
        cart = new Set();
    }

    const saveCart = () => {
        try {
            localStorage.setItem(storageKey, JSON.stringify([...cart]));
        } catch {
            // The in-memory cart still works when browser storage is unavailable.
        }
    };

    const hasFixedPriceCart = () => {
        const selectedServices = [...cart].map((serviceId) => serviceCatalog.get(serviceId));

        return selectedServices.length > 0
            && selectedServices.every((service) => service?.price > 0)
            && /^[A-Z]{3}$/.test(form.dataset.currency || '');
    };

    const updateButtons = () => {
        serviceButtons.forEach((button) => {
            const selected = cart.has(button.dataset.serviceId);
            const label = button.querySelector('[data-add-label]');
            const icon = button.querySelector('[data-add-icon]');

            button.setAttribute('aria-pressed', String(selected));

            if (label) label.textContent = selected ? 'Añadido al carrito' : 'Añadir al carrito';
            if (icon) icon.textContent = selected ? '✓' : '+';
        });
    };

    const renderCart = () => {
        cartList.replaceChildren();
        cartEmpty.hidden = cart.size > 0;
        const currency = form.dataset.currency;
        const fixedPriceCart = hasFixedPriceCart();
        const total = [...cart].reduce((sum, serviceId) => sum + (serviceCatalog.get(serviceId)?.price || 0), 0);
        const totalLabel = dialog.querySelector('[data-cart-total]');

        totalLabel.textContent = fixedPriceCart
            ? new Intl.NumberFormat(document.documentElement.lang, { style: 'currency', currency }).format(total)
            : 'Cotización personalizada';
        submitButton.textContent = fixedPriceCart ? 'Ir al pago de prueba' : 'Enviar solicitud de cotización';

        cart.forEach((serviceId) => {
            const item = document.createElement('li');
            const info = document.createElement('span');
            const title = document.createElement('strong');
            const detail = document.createElement('small');
            const remove = document.createElement('button');

            item.className = 'checkout-item';
            info.className = 'checkout-item-info';
            const service = serviceCatalog.get(serviceId);
            title.textContent = service.name;
            detail.textContent = service.price > 0 && /^[A-Z]{3}$/.test(currency || '')
                ? new Intl.NumberFormat(document.documentElement.lang, { style: 'currency', currency }).format(service.price)
                : 'Precio a definir en tu propuesta';
            remove.className = 'remove-item';
            remove.type = 'button';
            remove.dataset.removeService = serviceId;
            remove.setAttribute('aria-label', `Quitar ${service.name} del carrito`);
            remove.textContent = '×';

            info.append(title, detail);
            item.append(info, remove);
            cartList.append(item);
        });

        cartCount.textContent = String(cart.size);
        cartTrigger.setAttribute('aria-label', `Abrir carrito, ${cart.size} ${cart.size === 1 ? 'servicio' : 'servicios'}`);
        dialog.querySelector('[data-checkout-next="2"]').disabled = cart.size === 0;
        updateButtons();
    };

    const showStep = (step) => {
        dialog.querySelectorAll('[data-checkout-step]').forEach((panel) => {
            panel.hidden = panel.dataset.checkoutStep !== String(step);
        });

        dialog.querySelectorAll('[data-step-indicator]').forEach((indicator) => {
            if (indicator.dataset.stepIndicator === String(step)) {
                indicator.setAttribute('aria-current', 'step');
            } else {
                indicator.removeAttribute('aria-current');
            }
        });

        errorBox.hidden = true;

        const heading = dialog.querySelector(`[data-checkout-step="${step}"] h3`);
        heading?.focus({ preventScroll: true });
    };

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.hidden = false;
    };

    cartTrigger.addEventListener('click', () => {
        renderCart();
        showStep(1);
        dialog.showModal();
    });

    dialog.querySelectorAll('[data-checkout-close]').forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });

    dialog.addEventListener('close', () => {
        form.reset();
        showStep(1);
    });

    dialog.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-remove-service]');

        if (!removeButton) return;

        cart.delete(removeButton.dataset.removeService);
        saveCart();
        renderCart();
    });

    serviceButtons.forEach((button) => {
        button.addEventListener('click', () => {
            cart.add(button.dataset.serviceId);
            saveCart();
            renderCart();
            cartTrigger.setAttribute('data-cart-updated', 'true');
        });
    });

    dialog.querySelector('[data-checkout-next="2"]').addEventListener('click', () => showStep(2));
    dialog.querySelector('[data-checkout-back="1"]').addEventListener('click', () => showStep(1));
    dialog.querySelector('[data-checkout-back="2"]').addEventListener('click', () => showStep(2));

    dialog.querySelector('[data-checkout-next="3"]').addEventListener('click', () => {
        const requiredFields = [
            dialog.querySelector('#checkout-name'),
            dialog.querySelector('#checkout-email'),
            dialog.querySelector('#checkout-privacy'),
        ];
        const firstInvalid = requiredFields.find((field) => !field.checkValidity());

        if (firstInvalid) {
            firstInvalid.reportValidity();
            firstInvalid.focus();
            return;
        }

        showStep(3);
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (cart.size === 0) {
            showStep(1);
            showError('Añade al menos un servicio para continuar.');
            return;
        }

        const name = dialog.querySelector('#checkout-name');
        const email = dialog.querySelector('#checkout-email');
        const privacy = dialog.querySelector('#checkout-privacy');
        const firstInvalid = [name, email, privacy].find((field) => !field.checkValidity());

        if (firstInvalid) {
            showStep(2);
            firstInvalid.reportValidity();
            firstInvalid.focus();
            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = 'Enviando solicitud...';
        errorBox.hidden = true;

        const payload = new FormData(form);
        payload.delete('services[]');
        cart.forEach((serviceId) => payload.append('services[]', serviceId));

        try {
            const action = hasFixedPriceCart() ? form.action : form.dataset.quoteAction;
            const response = await fetch(action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                },
                body: payload,
            });
            const result = await response.json();

            if (!response.ok) {
                const validationMessages = Object.values(result.errors || {}).flat();
                showStep(validationMessages.length ? 2 : 3);
                showError(validationMessages.join(' ') || result.message || 'No pudimos enviar la solicitud. Inténtalo de nuevo.');
                return;
            }

            if (result.checkout_url) {
                window.location.assign(result.checkout_url);
                return;
            }

            dialog.querySelector('[data-checkout-confirmation]').textContent = result.message;
            form.reset();
            cart.clear();
            saveCart();
            renderCart();
            showStep(4);
        } catch {
            showError('No pudimos conectar con el servidor. Revisa tu conexión e inténtalo de nuevo.');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = hasFixedPriceCart() ? 'Ir al pago de prueba' : 'Enviar solicitud de cotización';
        }
    });

    const searchInput = document.querySelector('[data-catalog-search]');
    const searchForm = document.querySelector('[data-catalog-search-form]');
    const catalogStatus = document.querySelector('[data-catalog-status]');
    const catalogEmpty = document.querySelector('[data-catalog-empty]');
    const serviceCards = [...document.querySelectorAll('[data-service-card]')];

    const normalize = (value) => value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLocaleLowerCase('es');

    const filterServices = () => {
        const query = normalize(searchInput.value.trim());
        let visibleCount = 0;

        serviceCards.forEach((card) => {
            const matches = normalize(card.textContent).includes(query);
            card.hidden = !matches;
            if (matches) visibleCount += 1;
        });

        catalogEmpty.hidden = visibleCount > 0;
        catalogStatus.textContent = query
            ? `${visibleCount} ${visibleCount === 1 ? 'servicio encontrado' : 'servicios encontrados'}`
            : '';
    };

    searchInput.addEventListener('input', filterServices);
    searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        document.querySelector('#servicios').scrollIntoView({ behavior: 'smooth' });
    });

    renderCart();

    const hero = document.querySelector('.home-hero');
    const heroMessage = hero.querySelector('[data-hero-message]');
    const heroIndex = hero.querySelector('[data-slide-index]');
    const slides = [
        'Creamos páginas web, tiendas online y automatizamos tus procesos para que vendas más.',
        'Conecta con más clientes con una presencia digital clara, rápida y profesional.',
        'Digitalizamos las tareas de tu negocio para que puedas enfocarte en hacerlo crecer.',
    ];
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let activeSlide = 0;
    let rotationTimer;

    const showSlide = (index) => {
        activeSlide = (index + slides.length) % slides.length;
        heroMessage.textContent = slides[activeSlide];
        heroIndex.textContent = `0${activeSlide + 1} / 0${slides.length}`;
    };

    const stopRotation = () => window.clearInterval(rotationTimer);
    const startRotation = () => {
        stopRotation();
        if (!reduceMotion && !document.hidden) {
            rotationTimer = window.setInterval(() => showSlide(activeSlide + 1), 7000);
        }
    };

    hero.querySelector('[data-slide-previous]').addEventListener('click', () => {
        showSlide(activeSlide - 1);
        startRotation();
    });
    hero.querySelector('[data-slide-next]').addEventListener('click', () => {
        showSlide(activeSlide + 1);
        startRotation();
    });
    hero.addEventListener('mouseenter', stopRotation);
    hero.addEventListener('mouseleave', startRotation);
    hero.addEventListener('focusin', stopRotation);
    hero.addEventListener('focusout', (event) => {
        if (!hero.contains(event.relatedTarget)) startRotation();
    });
    document.addEventListener('visibilitychange', startRotation);
    startRotation();
})();