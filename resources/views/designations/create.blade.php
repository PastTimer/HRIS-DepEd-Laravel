@extends('layouts.app')
@section('title', 'Add Designation')
@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-white border-0">
                    <div class="row align-items-center">
                        <div class="col-8"><h3 class="mb-0">Add New Designation</h3></div>
                        <div class="col-4 text-right"><a href="/designations" class="btn btn-sm btn-primary">Back to List</a></div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="/designations">
                        @csrf
                        <div class="pl-lg-4">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Position Title *</label>
                                        <input type="text" name="title" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Position Type *</label>
                                        <select name="type" class="form-control" required>
                                            <option value="" disabled selected>Select Type</option>
                                            <option value="teaching">Teaching</option>
                                            <option value="nonteaching">Non-Teaching</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-control-label">Description</label>
                                        <textarea id="description" name="description" rows="4" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-success mt-4">Save Designation</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection