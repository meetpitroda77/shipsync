@extends('layouts.landinglayout')

@section('content')

  <div class="register-page bg-body-secondary ">
    @include('partials.toast')
    <div class="register-box  mt-5 mx-auto">
      <div class="card w-100 card-outline card-primary">
        <div class="card-header">
          <a href="/" class="link-dark text-center link-offset-2 link-opacity-100 link-opacity-50-hover">
            <h1 class="mb-0"><b> <img src="{{ Vite::asset('resources/img/shipcync.png') }}" alt=" Logo" width="150"
                  class="brand-image " />
              </b></h1>
          </a>
        </div>
        <div class="card-body register-card-body ">
          <p class="register-box-msg">Register a new user</p>

          <form action="{{ route('register.post') }}" method="post">
            @csrf
            <div class="row g-3">

              <div class="col-md-6">
                <div class="input-group mb-3">
                  <div class="form-floating">
                    <input id="registerFullName" type="text" class="form-control @error('name') is-invalid @enderror"
                      name="name" value="{{ old('name') }}" />
                    <label for="registerFullName">Full Name</label>
                  </div>
                  <div class="input-group-text">
                    <span class="bi bi-person"></span>
                  </div>
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="input-group mb-3">
                  <div class="form-floating">
                    <input id="registerEmail" type="email" class="form-control @error('email') is-invalid @enderror"
                      name="email" value="{{ old('email') }}" />
                    <label for="registerEmail">Email</label>
                  </div>
                  <div class="input-group-text">
                    <span class="bi bi-envelope"></span>
                  </div>
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="input-group mb-3">
                  <div class="form-floating">
                    <input id="registerPhone" type="text" class="form-control @error('phone') is-invalid @enderror"
                      name="phone" value="{{ old('phone') }}" />
                    <label for="registerPhone">Phone</label>
                  </div>
                  <div class="input-group-text">
                    <span class="bi bi-telephone"></span>
                  </div>
                  @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="input-group mb-3">
                  <div class="form-floating">
                    <input id="registerPassword" type="password"
                      class="form-control @error('password') is-invalid @enderror" name="password" />
                    <label for="registerPassword">Password</label>
                  </div>
                  <div class="input-group-text">
                    <span class="bi bi-lock-fill"></span>
                  </div>
                  @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>


              <div class="col-md-6">
                <div class="input-group mb-3">
                  <div class="form-floating">
                    <input id="registerCity" type="text" class="form-control @error('city') is-invalid @enderror"
                      name="city" value="{{ old('city') }}" />
                    <label for="registerCity">City</label>
                  </div>
                  <div class="input-group-text">
                    <span class="bi bi-building"></span>
                  </div>
                  @error('city')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="input-group mb-3">
                  <div class="form-floating">
                    <input id="registerState" type="text" class="form-control @error('state') is-invalid @enderror"
                      name="state" value="{{ old('state') }}" />
                    <label for="registerState">State</label>
                  </div>
                  <div class="input-group-text">
                    <span class="bi bi-flag-fill"></span>
                  </div>
                  @error('state')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Country -->
              <div class="col-md-6">
                <div class="input-group mb-3">
                  <div class="form-floating">
                    <input id="registerCountry" type="text" class="form-control @error('country') is-invalid @enderror"
                      name="country" value="{{ old('country') }}" />
                    <label for="registerCountry">Country</label>
                  </div>
                  <div class="input-group-text">
                    <span class="bi bi-globe"></span>
                  </div>
                  @error('country')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="input-group mb-3">
                  <div class="form-floating">
                    <input id="registerZipCode" type="text" class="form-control @error('zip_code') is-invalid @enderror"
                      name="zip_code" value="{{ old('zip_code') }}" />
                    <label for="registerZipCode">Zip Code</label>
                  </div>
                  <div class="input-group-text">
                    <span class="bi bi-mailbox"></span>
                  </div>
                  @error('zip_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-12">
                <div class="input-group mb-3">
                  <div class="form-floating">
                    <input id="registerAddress" type="text" class="form-control @error('address') is-invalid @enderror"
                      name="address" value="{{ old('address') }}" />
                    <label for="registerAddress">Address</label>
                  </div>
                  <div class="input-group-text">
                    <span class="bi bi-geo-alt-fill"></span>
                  </div>
                  @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>


            </div>



            <div class="row mt-3">
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Sign Up</button>
              </div>
              <!-- /.col -->
            </div>
            <!--end::Row-->
          </form>



          <p class="mb-0 mt-2">
            <a href="{{ route('login') }}" class="link-primary text-center"> I already have a registration </a>
          </p>
        </div>
        <!-- /.register-card-body -->
      </div>
    </div>
  </div>

@endsection