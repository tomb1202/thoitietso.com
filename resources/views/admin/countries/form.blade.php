@extends('admin.layouts.master')

@section('title')
    <title>{{ $country ? 'Chỉnh sửa quốc gia' : 'Thêm quốc gia' }}</title>
@endsection

@section('content')
    <section class="content-header">
        <ol class="breadcrumb">
            <li><a href="/admin"><i class="fa fa-home"></i>Trang chủ</a></li>
            <li><a href="{{ route('admin.countries.index') }}">Danh sách quốc gia</a></li>
            <li class="active">{{ $country ? 'Chỉnh sửa' : 'Thêm mới' }}</li>
        </ol>
        <div class="clearfix"></div>
    </section>

    <section class="content">
        <div class="box box-solid">
            <div class="box-body">
                <form action="{{ $country ? route('admin.countries.update', $country->id) : route('admin.countries.store') }}"
                      method="POST">
                    @csrf
                    @if ($country)
                        @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tên quốc gia <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                       value="{{ old('name', $country->name ?? '') }}">
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Slug</label>
                                <input type="text" name="slug" class="form-control"
                                       value="{{ old('slug', $country->slug ?? '') }}"
                                       placeholder="Tự động tạo nếu để trống">
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Hiển thị</label>
                                <select name="hidden" class="form-control select2">
                                    <option value="0" {{ old('hidden', $country->hidden ?? 0) == 0 ? 'selected' : '' }}>Hiện</option>
                                    <option value="1" {{ old('hidden', $country->hidden ?? 0) == 1 ? 'selected' : '' }}>Ẩn</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Meta Title</label>
                                <input type="text" name="meta_title" class="form-control"
                                       value="{{ old('meta_title', $country->meta_title ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description', $country->meta_description ?? '') }}</textarea>
                    </div>

                    <div class="form-group">
                        <a href="{{ route('admin.countries.index') }}" class="btn btn-danger">Huỷ bỏ</a>
                        <button type="submit" class="btn btn-success">Lưu lại</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
