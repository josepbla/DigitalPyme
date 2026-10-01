@extends('layouts.app')

@section('title', 'Política de Privacidad | DigitalPyme')

@section('content')
    <p class="eyebrow">Información legal</p>
    <h1>Política de Privacidad</h1>
    <p class="intro">Aquí explicamos qué información recibimos cuando solicitas un diagnóstico o una cotización, cómo la utilizamos y cómo puedes consultarnos sobre tus datos.</p>

    <nav class="privacy-toc" aria-label="Contenido de la política de privacidad">
        <h2>En esta página</h2>
        <ol>
            <li><a href="#responsable">Responsable y contacto</a></li>
            <li><a href="#datos">Datos que recopilamos</a></li>
            <li><a href="#uso">Uso de la información</a></li>
            <li><a href="#almacenamiento">Almacenamiento y conservación</a></li>
            <li><a href="#compartir">Acceso y comunicación de datos</a></li>
            <li><a href="#cookies">Cookies y pagos</a></li>
            <li><a href="#derechos">Tus solicitudes sobre datos</a></li>
        </ol>
    </nav>

    <article class="privacy-content">
        <section class="privacy-section" id="responsable" aria-labelledby="responsable-title">
            <h2 id="responsable-title">Responsable y contacto</h2>
            <p>DigitalPyme gestiona la información enviada desde este sitio para atender consultas de personas interesadas en sus servicios.</p>
            <p>El canal de contacto disponible es el <a href="{{ route('diagnostics.create') }}">formulario de diagnóstico</a>. Si tu consulta trata sobre privacidad, indícalo claramente en el mensaje para que podamos identificarla.</p>
        </section>

        <section class="privacy-section" id="datos" aria-labelledby="datos-title">
            <h2 id="datos-title">Datos que recopilamos</h2>
            <p>Para enviar una solicitud, el formulario pide tu nombre, correo electrónico, al menos un servicio de interés y tu autorización para usar los datos con el fin de responderte.</p>
            <p>También puedes proporcionar, de manera opcional, tu teléfono, el nombre de tu empresa, la dirección de tu sitio web y un mensaje. No incluyas contraseñas, datos bancarios ni otra información sensible.</p>
            <p>Cuando envías el formulario, quedan registrados la fecha de la solicitud y el momento en que aceptaste el aviso de privacidad, junto con los servicios seleccionados.</p>
        </section>

        <section class="privacy-section" id="uso" aria-labelledby="uso-title">
            <h2 id="uso-title">Uso de la información</h2>
            <p>Utilizamos la información para revisar tu situación, preparar una respuesta sobre el diagnóstico o la cotización solicitada y dar seguimiento a esa conversación.</p>
            <p>Si vuelves a escribir desde el mismo correo, la solicitud se asociará al registro de contacto correspondiente. Si decides pagar una contratación, Mercado Pago procesa los datos necesarios del pago en su propio checkout; DigitalPyme no recibe ni almacena los datos de tu tarjeta.</p>
        </section>

        <section class="privacy-section" id="almacenamiento" aria-labelledby="almacenamiento-title">
            <h2 id="almacenamiento-title">Almacenamiento y conservación</h2>
            <p>Los datos se guardan en la base de datos de la aplicación: los datos de contacto se registran por separado de cada solicitud y esta se relaciona con los servicios seleccionados.</p>
            <p>Actualmente no hay un borrado automático configurado. Las solicitudes pueden permanecer almacenadas mientras se gestionan y hasta que DigitalPyme las elimine. Puedes pedir que revisemos o eliminemos tus datos mediante el formulario de contacto.</p>
        </section>

        <section class="privacy-section" id="compartir" aria-labelledby="compartir-title">
            <h2 id="compartir-title">Acceso y comunicación de datos</h2>
            <p>La aplicación no vende tus datos ni integra servicios de publicidad. Si inicias un pago, compartiremos con Mercado Pago la información necesaria para crear el checkout, como el correo, los servicios y el importe. El proveedor de alojamiento puede tratar datos técnicos necesarios para que el sitio funcione.</p>
        </section>

        <section class="privacy-section" id="cookies" aria-labelledby="cookies-title">
            <h2 id="cookies-title">Cookies y pagos</h2>
            <p>El formulario puede utilizar cookies técnicas de sesión necesarias para mantener el estado de navegación y proteger el envío. Esta aplicación no tiene configuradas cookies de analítica ni publicidad.</p>
            <p>Los pagos se realizan en el checkout alojado por Mercado Pago. DigitalPyme no solicita ni almacena números de tarjeta, códigos de seguridad ni claves bancarias. Mercado Pago puede utilizar sus propias cookies y tecnologías para procesar la operación.</p>
        </section>

        <section class="privacy-section" id="derechos" aria-labelledby="derechos-title">
            <h2 id="derechos-title">Tus solicitudes sobre datos</h2>
            <p>Puedes contactarnos para consultar qué información compartiste o pedir que se corrija o elimine. Utiliza el <a href="{{ route('diagnostics.create') }}">formulario de diagnóstico</a> e indica que se trata de una solicitud de privacidad; usaremos el correo que proporciones para localizar el registro.</p>
            <p>El formulario registra la petición como una nueva solicitud de contacto. No envíes información sensible ni datos de pago para identificarte.</p>
        </section>
    </article>
@endsection