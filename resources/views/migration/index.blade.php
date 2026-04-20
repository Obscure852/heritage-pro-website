<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Migrations & Licensing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css" rel="stylesheet">

    <style>
        html,
        body {
            height: 100%;
        }

        .auth-full-page-content {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-md-4 col-lg-4 col-xl-5"> <!-- Adjusted for better centering -->
                <div class="auth-full-page-content d-flex p-sm-5 p-4">
                    <div class="w-100">
                        <div class="d-flex flex-column">
                            <!-- Logo and form content here -->
                            <div class="mb-md-5 text-center">
                                <a href="index" class="d-block auth-logo">
                                    <img src="{{ \App\Models\SchoolSetup::schoolLogo() }}"
                                        alt="Houdini 4 brand Identity" height="100">
                                </a>
                            </div>
                            <!-- Form starts -->
                            <div class="auth-content my-auto">
                                <div class="text-center">
                                    <h5 class="mb-0">Heritage Houdini 4 Pro System</h5>
                                </div>
                                <br>
                                <div style="display: none;" id="migration-error"
                                    class="alert alert-danger alert-border-left alert-dismissible fade show"
                                    role="alert">
                                    <i class="mdi mdi-block-helper me-3 align-middle"></i><strong>
                                        Error occurred running migrations.
                                    </strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>

                                <br>

                                <div style="display: none;" id="migration-success"
                                    class="alert alert-success alert-border-left alert-dismissible fade show"
                                    role="alert">
                                    <i class="mdi mdi-check-all me-3 align-middle"></i><strong>System migration was
                                        successful!</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>

                                <form method="POST" id="migrationForm" class="custom-form mt-4 pt-2">
                                    @csrf
                                    <div class="mb-3">
                                        <input style="text-align:center;" type="text"
                                            class="form-control form-control-sm @error('migration_code') is-invalid @enderror"
                                            name="migration_code" placeholder="Enter migration code"
                                            value="{{ old('migration_code') }}" required autofocus>
                                    </div>
                                    <div class="mb-3">
                                        <button class="btn btn-sm btn-primary w-100 waves-effect waves-light"
                                            type="submit">Run Migrations</button>
                                    </div>
                                </form>
                            </div>
                            <!-- Form ends -->
                            <!-- Footer -->
                            <div class="mt-4 mt-md-5 text-center">
                                <script>
                                    document.write(new Date().getFullYear())
                                </script> © Heritage. Crafted with <i
                                    class="mdi mdi-heart text-danger"></i> by Platinum Identity
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end auth full page content -->
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });

        $(document).ready(function() {
            $('#migrationForm').submit(function(event) {
                event.preventDefault();
                var formData = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: '{{ route('run.migrations') }}',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#migration-success').show();

                        } else {
                            $('#migration-error').show();
                        }
                    },
                    error: function() {
                        $('#migration-error').show();
                    }
                });
            });
        });
    </script>

</body>

</html>
