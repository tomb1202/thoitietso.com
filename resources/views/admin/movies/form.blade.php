@extends('admin.layouts.master')

@section('title')
    <title>Admin | Phim | Form</title>
@endsection

@section('content')
    <section class="content-header">
        <ol class="breadcrumb">
            <li><a href="/admin"><i class="fa fa-home"></i>Admin</a></li>
            <li class=""><a href="{{ route('admin.movies.index') }}">Phim</a></li>
        </ol>
        <div class="clearfix"></div>
    </section>

    <section class="content">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab-info" data-toggle="tab"><span class="text-bold">Thông tin</span></a></li>
            </ul>

            <form id="form-edit" class="tab-content"
                action="{{ !empty($movie->id) ? route('admin.movies.update', $movie->id) : route('admin.movies.store') }}"
                method="post" enctype="multipart/form-data">

                <div id="deleted-episodes-container"></div>
                @csrf

                @if (!empty($movie->id))
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $movie->id }}">
                    @include('admin.movies.update.info')
                @else
                    @include('admin.movies.create.info')
                @endif

                <div class="box-footer">
                    <a href="{{ route('admin.movies.index') }}" class="btn btn-danger">Huỷ bỏ</a>
                    <button type="submit" name="action" value="save_add" class="btn btn-success">Cập nhật</button>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('script')
    @include('admin.movies.script')
@endsection
