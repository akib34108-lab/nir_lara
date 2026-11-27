@include('layouts.partials.header')
<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth px-0">
        <div class="row w-100 mx-0">
          <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
              <div class="brand-logo">
                <img src="{{ asset('assets/images/logo.svg') }}" alt="logo">
              </div>
              <h4>New here?</h4>
              <h6 class="font-weight-light">Signing up is easy. It only takes a few steps</h6>
              <form class="pt-3" method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-group">
                  <input type="text" name="name" class="form-control form-control-lg" id="exampleInputUsername1" placeholder="Username" required>
                </div>
                <div class="form-group">
                  <input type="email" name="email" class="form-control form-control-lg" id="exampleInputEmail1" placeholder="Email" required>
                </div>
                <div class="form-group">
                  <div class="input-group">
                    <input id="password" type="password" name="password" class="form-control form-control-lg @error('password') is-invalid @enderror" placeholder="Password" required autocomplete="new-password">
                    <div class="input-group-append">
                        <span class="input-group-text" style="cursor: pointer; background: #fff;" onclick="togglePassword('password', this)">
                            <i class="ti-eye"></i>
                        </span>
                    </div>
                </div>
                @error('password')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
                </div>
                <div class="form-group">
                    <div class="input-group">
                      <input id="password-confirm" type="password" name="password_confirmation" class="form-control form-control-lg" placeholder="Confirm Password" required autocomplete="new-password">
                      <div class="input-group-append">
                          <span class="input-group-text" style="cursor: pointer; background: #fff;" onclick="togglePassword('password-confirm', this)">
                              <i class="ti-eye"></i>
                          </span>
                      </div>
                  </div>
                </div>
                <div class="mb-4">
                  <div class="form-check">
                    <label class="form-check-label text-muted">
                      <input type="checkbox" name="terms" class="form-check-input" required>
                      I agree to all Terms & Conditions
                    </label>
                  </div>
                </div>
                <div class="mt-3">
                  <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
                    SIGN UP
                  </button>
                </div>
                <div class="text-center mt-4 font-weight-light">
                  Already have an account? <a href="{{ route('login') }}" class="text-primary">Login</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <!-- content-wrapper ends -->
    </div>
    <!-- page-body-wrapper ends -->
  </div>
  <script src="{{ asset('assets/js/custom-js/eye-button.js') }}"></script>
  @include('layouts.partials.footer')
