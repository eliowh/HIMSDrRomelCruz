@extends('layouts.doctor')

@section('title','Doctor Dashboard')

@section('content')
@php
    $doctorName = auth()->user()->name ?? 'Doctor';
@endphp

<div style="padding: 20px;">
    <h2>Welcome, Dr. {{ $doctorName }}!</h2>
    

@endsection
