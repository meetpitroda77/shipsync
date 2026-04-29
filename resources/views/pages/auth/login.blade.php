@extends('layouts.landinglayout')

@section('content')


  <div class="login-page bg-body-secondary ">

    <div class="login-box ">
      <div class="card card-outline card-primary ">
        <div class="card-header">
          <a href="/" class="link-dark text-center link-offset-2 link-opacity-100 link-opacity-50-hover">
            <h1 class="mb-0"><b> <img src="{{ Vite::asset('resources/img/shipcync.png') }}" alt=" Logo" width="150"
                  class="brand-image " />
              </b></h1>
          </a>
        </div>
        <div class="card-body login-card-body ">
          <p class="login-box-msg">Sign in </p>

          <form action="{{ route('login.post') }}" method="POST">
            @csrf

            <div class="input-group mt-2 mb-4">
              <div class="form-floating">
                <input id="loginEmail" type="email" name="email"
                  class="form-control @error('email') is-invalid @enderror " value="{{ old('email') }}"
                  placeholder="Email" />
                <label for="loginEmail">Email</label>
              </div>
              <div class="input-group-text">
                <span class="bi bi-envelope"></span>
              </div>

              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>


            <div class="input-group mt-2 mb-4">
              <div class="form-floating">
                <input id="loginPassword" type="password" name="password" value="{{ old('password') }}"
                  class="form-control @error('password') is-invalid @enderror" placeholder="Password" />
                <label for="loginPassword">Password</label>
              </div>
              <div class="input-group-text">
                <span class="bi bi-lock-fill"></span>
              </div>
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>



            <div class="d-grid mt-2 gap-2">
              <button type="submit" class="btn btn-primary">Sign In</button>
            </div>

            <p class="mb-1 mt-2">
              <a href="{{ route('password.request') }}">I forgot my password</a>
            </p>
            <p class="mb-0 ">
              <a href="{{ route('register') }}" class="text-center"> Register a new user </a>
            </p>
          </form>
        </div>
      </div>
    </div>

@endsection