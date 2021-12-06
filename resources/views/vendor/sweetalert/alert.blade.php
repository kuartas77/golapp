<script src="{{ asset('vendor/sweetalert/sweetalert.all.js') }}"></script>
@if (Session::has('alert.config'))
    <script>
        let config = {!! Session::get('alert.config') !!};
        // console.log(config)
        config.type = config.icon;
        delete config.icon
        delete config.customClass
        delete config.timerProgressBar
        swal(config);
    </script>
@endif
@if ($errors->any())
    <script>
        let error_conf = {
            heightAuto: false,
            padding: "1.25rem",
            showCloseButton: false,
            showConfirmButton: true,
            timer: 5000,
            title: "Errores",
            type: "error",
            width: "32rem"
        }
        let text = ""
        @foreach ($errors->all() as $error)
            text+= "{{ $error }}\n";
        @endforeach
            error_conf.text = text;
        swal(error_conf);
    </script>
@endif
