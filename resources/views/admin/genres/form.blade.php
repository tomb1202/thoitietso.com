@extends('admin.layouts.master')

@section('title')
    <title>{{ $genre ? 'Chỉnh sửa chuyên mục' : 'Thêm chuyên mục' }}</title>
@endsection

@section('content')
    <section class="content-header">
        <ol class="breadcrumb">
            <li><a href="/admin"><i class="fa fa-home"></i>Trang chủ</a></li>
            <li><a href="{{ route('admin.genres.index') }}">Danh sách chuyên mục</a></li>
            <li class="active">{{ $genre ? 'Chỉnh sửa' : 'Thêm mới' }}</li>
        </ol>
        <div class="clearfix"></div>
    </section>

    <section class="content">
        <div class="box box-solid">
            <div class="box-body">
                <form action="{{ $genre ? route('admin.genres.update', $genre->id) : route('admin.genres.store') }}"
                    method="POST">
                    @csrf
                    @if ($genre)
                        @method('PUT')
                    @endif

                    <div class="row">
                        {{-- Tên chuyên mục --}}
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Tên chuyên mục <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                    value="{{ old('name', $genre->name ?? '') }}">
                            </div>
                        </div>

                        {{-- Slug --}}
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Slug</label>
                                <input type="text" name="slug" class="form-control"
                                    value="{{ old('slug', $genre->slug ?? '') }}" placeholder="Tự động tạo nếu để trống">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Sắp xếp</label>
                                <input type="number" name="sort" class="form-control"
                                    value="{{ old('sort', $genre->sort ?? 0) }}">
                            </div>
                        </div>

                        {{-- Hiển thị --}}
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Hiển thị</label>
                                <select name="hidden" class="form-control select2">
                                    <option value="0" {{ old('hidden', $genre->hidden ?? 0) == 0 ? 'selected' : '' }}>
                                        Hiện</option>
                                    <option value="1" {{ old('hidden', $genre->hidden ?? 0) == 1 ? 'selected' : '' }}>
                                        Ẩn</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Là menu</label>
                                <select name="is_main" class="form-control select2">
                                    <option value="0" {{ old('is_main', $genre->is_main ?? 0) == 0 ? 'selected' : '' }}>
                                        Không</option>
                                    <option value="1" {{ old('is_main', $genre->is_main ?? 0) == 1 ? 'selected' : '' }}>
                                        Là menu</option>
                                </select>
                            </div>
                        </div>


                    </div>
                    {{-- Meta title --}}
                    <div class="form-group">
                        <label>Meta Title</label>
                        <input type="text" name="meta_title" class="form-control"
                            value="{{ old('meta_title', $genre->meta_title ?? '') }}">
                    </div>

                    {{-- Meta Description --}}
                    <div class="form-group">
                        <label>Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description', $genre->meta_description ?? '') }}</textarea>
                    </div>

                    <div class="form-group">
                        <a href="{{ route('admin.genres.index') }}" class="btn btn-danger">Huỷ bỏ</a>
                        <button type="submit" class="btn btn-success">Lưu lại</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
