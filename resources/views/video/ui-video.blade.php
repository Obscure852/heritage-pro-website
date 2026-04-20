@extends('layouts.master')

@section('title')
    Heritage Tutorials
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="#">Video</a>
        @endslot
        @slot('title')
            Heritage Pro Tutorials
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Students Module</h4>
                    <p class="card-title-desc">To learn how to add a new student watch this video</p>
                </div><!-- end card header -->

                <div class="card-body">
                    <!-- 21:9 aspect ratio -->
                    <div class="ratio ratio-21x9">
                        <iframe src="https://www.youtube.com/watch?v=30RdLn6bGf0" title="YouTube video"
                            allowfullscreen></iframe>
                    </div>

                </div><!-- end card-body -->
            </div><!-- end card -->
        </div><!-- end col -->

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Academic Management</h4>
                    <p class="card-title-desc">To learn how to allocate a new student watch this video</p>
                </div><!-- end card header -->

                <div class="card-body">
                    <!-- 21:9 aspect ratio -->
                    <div class="ratio ratio-21x9">
                        <iframe src="https://www.youtube.com/watch?v=d7XeSgSIn8c&t=5s" title="YouTube video"
                            allowfullscreen></iframe>
                    </div>

                </div><!-- end card-body -->
            </div><!-- end card -->
        </div><!-- end col -->
    </div> <!-- end row -->
@endsection
