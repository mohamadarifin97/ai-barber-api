<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Ai BARBER</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    {{-- jquery --}}
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    {{-- toastr --}}
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
</head>
<body>
    <div class="container" style="padding: 15% 10%">
        <h3 class="text-center">Selamat Datang ke Ai BARBER</h3>

        <div class="row justify-content-center">
          <div class="card" style="width:40%">
              <div class="card-body">
                <p class="text-danger">Sila masukkan no telefon yang mempunyai Whatsapp</p>
                <form id="form" action="" enctype="multipart/form-data">
                  @csrf
                  <label for="telNo" class="form-label">No Telefon</label>
                  <input type="text" class="form-control" id="telNo" name="tel_no" aria-describedby="No Telefon" placeholder="10 hingga 11 digit no. telefon" onkeypress="return onlyNumberKey(event)" maxlength="11">
                  <div class="float-end mt-2">
                    <input type="button" onclick="test(); return false;" class="btn btn-success btn-sm" value="Hantar">
                  </div>
                </form>
              </div>
            </div>
        </div>
    </div>
</body>
<script>
    var APP_URL = {!! json_encode(url('/')) !!}

    function test() {
      $('#telNo').removeClass('is-invalid')
      $('.invalid-feedback').remove()

      let no = $('#telNo').val()

      $.ajax({
          url: APP_URL + '/api/get-queue',
          type: 'GET',
          data: {tel_no: no},
          success: function(response) {
              if (response.status == 'success') {
                  toastr.success('Berjaya! Anda Akan Menerima No. Giliran Melalui Whatsapp!')
              } else {
                  toastr.error('Ralat! Hubungi Sistem Admin')
              }

              $('#form')[0].reset()
          },
          error: function(error) {
            if (error.responseJSON) {
              $('#telNo').addClass('is-invalid')
              $(`<span class="invalid-feedback d-block" role="alert"><strong>No Telefon perlulah di antara 10 dan 11 digit</strong></span>`).insertAfter('#telNo')
            } else {
              toastr.error('Ralat! Hubungi Sistem Admin')
            }
          }
      });
    }

    function onlyNumberKey(evt) {
              
              // Only ASCII character in that range allowed
              var ASCIICode = (evt.which) ? evt.which : evt.keyCode
              if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57))
                  return false;
              return true;
          }
</script>
</html>