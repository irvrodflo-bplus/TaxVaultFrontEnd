<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Bóveda Fiscal</title>
    <link href="css/styles.css" rel="stylesheet" />
</head>

<body>
    <style>
        .auth-wrapper-custom {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: url('./img/background.jfif') no-repeat center center;
            background-size: cover;
            position: relative;
            padding: 20px;
        }

        .auth-wrapper-custom::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 80, 120, 0.2);
            z-index: 0;
        }

        .auth-box-custom {
            position: relative;
            background: #fff;
            padding: 40px 30px 30px 30px;
            width: 100%;
            max-width: 420px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 80, 120, 0.3);
            z-index: 1;
            color: rgb(0, 80, 120);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            user-select: none;
            transition: box-shadow 0.3s ease;
        }

        .auth-box-custom:hover {
            box-shadow: 0 25px 45px rgba(0, 80, 120, 0.5);
        }

        .logo {
            margin-bottom: 25px;
        }

        .font-medium {
            font-weight: 600;
            color: rgb(0, 70, 110);
        }

        .form-horizontal-custom {
            display: flex;
            flex-direction: column;
        }

        .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border: 2px solid rgba(0, 80, 120, 0.3);
            border-radius: 8px;
            background: #fff;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .input-group-custom:focus-within {
            border-color: rgb(0, 90, 135);
            box-shadow: 0 0 8px rgba(0, 90, 135, 0.3);
        }

        .input-icon {
            padding: 0 12px;
            color: rgb(0, 80, 120);
            font-size: 1.1rem;
            user-select: none;
            pointer-events: none;
        }

        .form-control-custom {
            flex: 1;
            border: none;
            outline: none;
            padding: 12px 15px;
            font-size: 1rem;
            color: #333;
            background: transparent;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-control-custom::placeholder {
            color: #aaa;
            font-style: italic;
            transition: opacity 0.3s ease;
        }

        .form-control-custom:focus::placeholder {
            opacity: 0.5;
        }

        .input-icon-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: rgb(0, 80, 120);
            cursor: pointer;
            font-size: 1.15rem;
            transition: color 0.25s ease;
            user-select: none;
        }

        .input-icon-toggle:hover {
            color: rgb(0, 100, 150);
        }

        .btn-custom {
            background-color: rgb(0, 80, 120);
            color: #fff;
            border: none;
            padding: 14px 0;
            font-size: 1.15rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.25s ease;
            width: 100%;
            user-select: none;
        }

        .btn-custom:hover,
        .btn-custom:focus {
            background-color: rgb(0, 100, 150);
            box-shadow: 0 0 15px rgba(0, 100, 150, 0.5);
            outline: none;
        }

        .form-group-custom {
            margin-top: 10px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.5s ease forwards;
        }

    </style>
    <div class="main-wrapper">
        <!-- Login box -->
        <div class="auth-wrapper-custom">
            <div class="auth-box-custom fade-in-up">
                <div>
                    <div class="logo text-center">
                        <span class="db"><img src="./img/humanergy-complete.png" alt="logo" width="25%"></span>
                        <h5 class="font-medium mb-3">Iniciar sesión</h5>
                    </div>

                    <!-- Form -->
                    <form id="loginForm" method="POST" class="form-horizontal-custom">
                        <div class="input-group-custom mb-3">
                            <div class="input-icon">
                                <i class="ti-user"></i>
                            </div>
                            <input type="email"
                                id="email"
                                name="email"
                                class="form-control-custom"
                                placeholder="Correo Electrónico"
                                required>
                        </div>

                        <div class="input-group-custom mb-3 position-relative">
                            <div class="input-icon">
                                <i class="ti-pencil"></i>
                            </div>
                            <input type="password"
                                id="password"
                                name="password"
                                class="form-control-custom"
                                placeholder="Contraseña"
                                required>
                            <span class="input-icon-toggle" onclick="showPassword()" role="button" aria-label="Mostrar/Ocultar contraseña">
                                <i class="fa fa-eye" id="togglePassword"></i>
                            </span>
                        </div>

                        <div class="form-group-custom text-center">
                            <button onclick="login()" class="btn-custom" type="submit" id="inicio-sesion">
                                Iniciar sesión
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- End Login box -->
    </div>

    <script defer src="https://use.fontawesome.com/releases/v5.15.4/js/all.js" integrity="sha384-rOA1PnstxnOBLzCLMcre8ybwbTmemjzdNlILg8O7z1lUkLXozs4DHonlDtnE7fpc" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script>
        function login() {
           window.location.href = 'index.php';
        }
    </script>
</body>
</html>