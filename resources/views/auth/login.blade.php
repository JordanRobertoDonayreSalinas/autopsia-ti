<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- CORRECCIÓN: Agregar el meta del token CSRF para AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bienvenido - Sistema</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #dbeafe;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .main-card {
            background: white;
            width: 100%;
            max-width: 900px;
            min-height: 550px;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            display: flex;
            overflow: hidden;
            position: relative;
        }

        .left-side {
            width: 50%;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            position: relative;
        }

        .left-side::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: #eff6ff;
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .lottie-wrapper {
            width: 100%;
            max-width: 350px;
            position: relative;
            z-index: 10;
        }

        .right-side {
            width: 50%;
            padding: 40px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
        }

        @media (max-width: 768px) {
            .main-card {
                flex-direction: column;
                max-width: 450px;
            }

            .left-side,
            .right-side {
                width: 100%;
            }

            .left-side {
                padding: 30px;
                min-height: 250px;
            }

            .lottie-wrapper {
                max-width: 200px;
            }
        }

        .title {
            font-size: 28px;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 5px;
            text-align: center;
        }

        .subtitle {
            font-size: 14px;
            color: #64748b;
            text-align: center;
            margin-bottom: 35px;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .custom-input {
            width: 100%;
            padding: 15px 20px 15px 45px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            font-size: 14px;
            outline: none;
            transition: all 0.3s;
            box-sizing: border-box;
            color: #334155;
        }

        .custom-input:focus {
            background: white;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            color: #94a3b8;
            pointer-events: none;
        }

        .btn-primary {
            width: 100%;
            padding: 16px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }

        .error-text {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
            display: block;
        }
    </style>
</head>

<body>

    <div class="main-card">
        <div class="left-side">
            <div class="lottie-wrapper">
                <lottie-player src="{{ asset('assets/login.json') }}" background="transparent" speed="1"
                    style="width: 100%; height: 100%;" loop autoplay>
                </lottie-player>
            </div>
        </div>

        <div class="right-side">
            <div class="text-center">
                <h1 class="title">Bienvenido</h1>
                <p class="subtitle">Ingresa tus credenciales para acceder al sistema</p>
            </div>

            {{-- FORMULARIO --}}
            <form id="loginForm" action="{{ route('login') }}" method="POST">
                {{-- CORRECCIÓN: El CSRF es vital para evitar el Error 419 --}}
                @csrf

                <div class="input-group">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <input type="text" name="username" required maxlength="8" placeholder="Usuario"
                        class="custom-input" value="{{ old('username') }}">
                    @error('username')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <input type="password" name="password" required placeholder="Contraseña" class="custom-input">
                    @error('password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" id="submitBtn" class="btn-primary">
                    <span id="btnText">Ingresar</span>
                </button>
            </form>
        </div>
    </div>

    {{-- SCRIPT PARA MANEJO DE AJAX Y ANIMACIÓN --}}
    <script>
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = this;
            const btn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');

            // Animación de carga
            btn.disabled = true;
            btnText.innerHTML = "Verificando...";

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Acceso Correcto!',
                            text: 'Redirigiendo...',
                            timer: 1500,
                            showConfirmButton: false,
                            willClose: () => {
                                window.location.href = data.redirect;
                            }
                        });
                    } else {
                        throw new Error(data.message || 'Credenciales inválidas');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de acceso',
                        text: error.message,
                        confirmButtonColor: '#2563eb'
                    });
                    btn.disabled = false;
                    btnText.innerHTML = "Ingresar";
                });
        });
    </script>
</body>

</html>