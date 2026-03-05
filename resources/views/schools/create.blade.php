@extends('layouts.app')
@section('title', 'Add School')
@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-white border-0">
                    <div class="row align-items-center">
                        <div class="col-8"><h3 class="mb-0">Add New School / Station</h3></div>
                        <div class="col-4 text-right"><a href="/schools" class="btn btn-sm btn-primary">Back to List</a></div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="/schools">
                        @csrf
                        <div class="pl-lg-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-control-label">School ID *</label>
                                        <input type="text" name="school_id" class="form-control" placeholder="e.g. 101123" required>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="form-control-label">School Name *</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">District</label>
                                        <input type="text" name="district" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Complete Address</label>
                                        <input type="text" name="address" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-success mt-4">Save School</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection