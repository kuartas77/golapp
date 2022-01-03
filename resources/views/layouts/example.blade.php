@extends('layouts.app')
@section('title', 'Inicio')
@section('content')
@include('templates.bread_crumb', ['title' => 'Example', 'option' => 0])
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
            </div>
        </div>
    </div>
</div>
@endsection
@section('modals')
@endsection
@section('scripts')
<script></script>
@endsection

@extends('layouts.app')
@section('content')
    <x-bread-crumb title="Asistencias" :option="0"/>
    <x-row-card col-inside="8" col-outside="2" >
        
    </x-row-card >
    <x-row-card col-inside="12" >

    </x-row-card >
@endsection
@section('modals')
@endsection
@section('scripts')
    <script>
        
    </script>
    
@endsection
