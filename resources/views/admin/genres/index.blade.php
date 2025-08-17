@extends('admin.layouts.master')

@section('title')
    <title>Quản lý chuyên mục</title>
@endsection

@section('content')
    <section class="content-header">
        <ol class="breadcrumb">
            <li><a href="/admin"><i class="fa fa-home"></i>Trang chủ</a></li>
            <li class="active">Danh sách chuyên mục</li>
        </ol>
        <ul class="right-button">
            <li>
                <a href="{{ route('admin.genres.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Thêm chuyên mục
                </a>
            </li>
        </ul>
        <div class="clearfix"></div>
    </section>

    <section class="content">
        <div class="box box-solid">
            <div class="box-body">
                <form method="GET" class="mb-3">
                    <div class="row">
                        <div class="col-sm-4">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   class="form-control" placeholder="Tìm theo tên...">
                        </div>
                        <div class="col-sm-2">
                            <button class="btn btn-success">Tìm kiếm</button>
                        </div>
                    </div>
                </form>

                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tên</th>
                            <th>Slug</th>
                            <th>Sắp xếp</th>
                            <th>Ẩn</th>
                            <th>Cập nhật</th>
                            <th>Phim</th>
                            <th>Là menu</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $index => $genre)
                            <tr>
                                <td>{{ $data->firstItem() + $index }}</td>
                                <td>{{ $genre->name }}</td>
                                <td>{{ $genre->slug }}</td>
                                <td>{{ $genre->id }}</td>
                                <td>
                                    @if ($genre->hidden == 1)
                                        <span class="label label-danger">Ẩn</span>
                                    @else
                                        <span class="label label-success">Hiện</span>
                                    @endif
                                </td>
                                <td>{{ $genre->updated_at ? $genre->updated_at->format('d/m/Y H:i') : '---' }}</td>
                                <td>{{ $genre->movies_count }}</td>

                                <td>
                                    @if ($genre->is_main == 0)
                                        <span class="label label-danger">Không</span>
                                    @else
                                        <span class="label label-success">Là Menu</span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('admin.genres.edit', $genre->id) }}"
                                       class="btn btn-sm btn-primary"><i class="fa fa-pencil"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {!! $data->appends(request()->input())->links('admin.widgets.default') !!}
            </div>
        </div>
    </section>
@endsection
