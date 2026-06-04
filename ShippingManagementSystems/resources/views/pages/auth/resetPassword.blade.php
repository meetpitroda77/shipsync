@extends('layouts.landinglayout')

@section('content')





    <div class="bg-light py-3 py-md-5">
        <div class="container">
            <div class="row justify-content-md-center">
                <div class="col-12 col-md-11 col-lg-8 col-xl-7 col-xxl-6">
                    <div class="bg-white p-4 p-md-5 rounded shadow-sm">
                        <div class="row gy-3 mb-5">
                            <div class="col-12">
                                <div class="text-center">
                                    <a href="/">
                                        <img src="{{ Vite::asset('resources/img/shipcync.png') }}" width="150"
                                            alt="BootstrapBrain Logo">
                                    </a>
                                </div>
                            </div>

                        </div>
                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="row gy-3 gy-md-4 overflow-hidden">
                                <div class="col-12">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" class="bi bi-envelope" viewBox="0 0 16 16">
                                                <path
                                                    d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z" />
                                            </svg>
                                        </span>
                                        <input type="email" class="form-control" name="email" placeholder="Enter your email"
                                            id="email" value="{{ old('email') }}">
                                    </div>
                                    @error('email')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="password" class="form-label"> New Password <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" class="bi bi-lock" viewBox="0 0 16 16">
                                                <path
                                                    d="M8 1a2 2 0 0 0-2 2v4H3a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h7a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1h-3V3a2 2 0 0 0-2-2zM5.5,5.5A.5.5,0,0,1,6,5h4a.5.5,0,0,1,.5.5v4a.5.5,0,0,1-.5.5H6a.5.5,0,0,1-.5-.5V5.5z" />
                                            </svg>
                                        </span>
                                        <input type="password" class="form-control" name="password"
                                            placeholder="Confirm your password" id="password" value="{{ old('password') }}">
                                    </div>
                                    @error('password')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="password_confirmation" class="form-label">Confirm Password <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" class="bi bi-lock" viewBox="0 0 16 16">
                                                <path
                                                    d="M8 1a2 2 0 0 0-2 2v4H3a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h7a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1h-3V3a2 2 0 0 0-2-2zM5.5,5.5A.5.5,0,0,1,6,5h4a.5.5,0,0,1,.5.5v4a.5.5,0,0,1-.5.5H6a.5.5,0,0,1-.5-.5V5.5z" />
                                            </svg>
                                        </span>
                                        <input type="password" class="form-control" name="password_confirmation"
                                            placeholder="Confirm your password" id="password_confirmation"
                                            value="{{ old('password_confirmation') }}">
                                    </div>

                                    @error('password_confirmation')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <div class="d-grid">
                                        <button class="btn btn-primary btn-lg" type="submit">Reset Password</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection