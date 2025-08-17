@extends('admin.layouts.master')

@section('title')
    <title>{{ $actor ? 'Chỉnh sửa diễn viên' : 'Thêm diễn viên' }}</title>
@endsection

@section('content')
    <section class="content-header">
        <ol class="breadcrumb">
            <li><a href="/admin"><i class="fa fa-home"></i>Trang chủ</a></li>
            <li><a href="{{ route('admin.actors.index') }}">Danh sách diễn viên</a></li>
            <li class="active">{{ $actor ? 'Chỉnh sửa' : 'Thêm mới' }}</li>
        </ol>
        <div class="clearfix"></div>
    </section>

    <section class="content">
        <div class="box box-solid">
            <div class="box-body">
                <form action="{{ $actor ? route('admin.actors.update', $actor->id) : route('admin.actors.store') }}"
                      method="POST">
                    @csrf
                    @if ($actor)
                        @method('PUT')
                    @endif

                    <div class="form-group">
                        <label>Tên diễn viên <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                               value="{{ old('name', $actor->name ?? '') }}">
                    </div>

                    <div class="form-group">
                        <a href="{{ route('admin.actors.index') }}" class="btn btn-danger">Huỷ bỏ</a>
                        <button type="submit" class="btn btn-success">Lưu lại</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
