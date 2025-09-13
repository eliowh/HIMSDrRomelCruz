@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ url('css/nurse_addPatients.css') }}">
<div class="nurse-card">
    <h3>Add Patient</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ url('/nurse/addPatients') }}" method="POST">
        @csrf

        <label>First Name</label>
        <input type="text" name="first_name" required value="{{ old('first_name') }}">

        <label>Middle Name</label>
        <input type="text" name="middle_name" value="{{ old('middle_name') }}">

        <label>Last Name</label>
        <input type="text" name="last_name" required value="{{ old('last_name') }}">

        <label>Date of Birth</label>
        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}">

        <div class="form-row">
            <div class="form-col">
                <label>Age</label>
                <div class="age-inputs">
                    <div class="age-item">
                        <label class="small-label">(years)</label>
                        <input type="number" name="age_years" min="0" value="{{ old('age_years') }}">
                    </div>
                    <div class="age-item">
                        <label class="small-label">(months)</label>
                        <input type="number" name="age_months" min="0" value="{{ old('age_months') }}">
                    </div>
                    <div class="age-item">
                        <label class="small-label">(days)</label>
                        <input type="number" name="age_days" min="0" value="{{ old('age_days') }}">
                    </div>
                </div>
            </div>
        </div>

        <label>Province</label>
        <select name="province">
            <option value="Bulacan" {{ old('province','Bulacan')=='Bulacan' ? 'selected':'' }}>Bulacan</option>
            <!-- add others if needed -->
        </select>

        <label>City</label>
        <select name="city">
            <option value="Malolos City" {{ old('city','Malolos City')=='Malolos City' ? 'selected':'' }}>Malolos City</option>
            <!-- add others if needed -->
        </select>

        <label>Barangay</label>
        <input type="text" name="barangay" value="{{ old('barangay') }}">

        <label>Nationality</label>
        <input type="text" name="nationality" value="{{ old('nationality','Filipino') }}">

        <p style="font-size:0.9em;color:#666">Patient No will be assigned automatically (starts at 250001).</p>

        <button type="submit">Create Patient</button>
    </form>
</div>
@endsection