@extends('layouts.doctor')

@section('title','Test Doctor Page')

@section('content')

<div style="padding: 20px;">
    <h1>Doctor Test Page</h1>
    <p>This is a test page to verify doctor permissions work.</p>
    <p>Current user: {{ auth()->user()->name ?? 'Not logged in' }}</p>
    <p>Current role: {{ auth()->user()->role ?? 'No role' }}</p>
    <p>Time: {{ now() }}</p>
</div>

@endsection