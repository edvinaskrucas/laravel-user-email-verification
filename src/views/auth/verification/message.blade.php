@extends('layouts.app')

<!-- Main Content -->
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Verify Account</div>
                    <div class="panel-body">
                        In order to verify your account, you have to click on the link in your inbox.
                    </div>
                    <div class="panel-footer text-right">
                        <a href="{{ route('verification.resend') }}">Resend Link</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
