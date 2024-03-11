@extends('layouts.app')

@section('content')
<x-bread-crumb title="{{__('Verify Your Email Address')}}" :option="0"/>
<x-row-card>
    <div class="card-header">{{ __('Verify Your Email Address') }}</div>

    <div class="card-body">
        @if (session('resent'))
            <div class="alert alert-success" role="alert">
                {{ __('A fresh verification link has been sent to your email address.') }}
            </div>
        @endif

        {{ __('Before proceeding, please check your email for a verification link.') }}
        {{ __('If you did not receive the email') }},
        <a href="{{ route('verification.resend') }}" onclick="event.preventDefault(); document.getElementById('verification-resend').submit();">
        {{ __('click here to request another') }}</a>.
        <form id="verification-resend" action="{{ route('verification.resend') }}" method="POST"
                style="display: none;">
            @csrf
        </form>
    </div>
</x-row-card>
@endsection
