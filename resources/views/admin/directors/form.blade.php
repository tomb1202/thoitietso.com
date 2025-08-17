@extends('admin.layouts.master')

@section('title')
    <title>{{ $director ? 'Chỉnh sửa đạo diễn' : 'Thêm đạo diễn' }}</title>
@endsection

@section('content')
    <section class="content-header">
        <ol class="breadcrumb">
            <li><a href="/admin"><i class="fa fa-home"></i>Trang chủ</a></li>
            <li><a href="{{ route('admin.directors.index') }}">Danh sách đạo diễn</a></li>
            <li class="active">{{ $director ? 'Chỉnh sửa' : 'Thêm mới' }}</li>
        </ol>
        <div class="clearfix"></div>
    </section>

    <section class="content">
        <div class="box box-solid">
            <div class="box-body">
                <form action="{{ $director ? route('admin.directors.update', $director->id) : route('admin.directors.store') }}"
                      method="POST">
                    @csrf
                    @if ($director)
                        @method('PUT')
                    @endif

                    <div class="row">
                        {{-- Tên đạo diễn --}}
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Tên đạo diễn <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                       value="{{ old('name', $director->name ?? '') }}">
                            </div>
                        </div>

                        {{-- Slug --}}
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Slug</label>
                                <input type="text" name="slug" class="form-control"
                                       value="{{ old('slug', $director->slug ?? '') }}"
                                       placeholder="Tự động tạo nếu để trống">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <a href="{{ route('admin.directors.index') }}" class="btn btn-danger">Huỷ bỏ</a>
                        <button type="submit" class="btn btn-success">Lưu lại</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
