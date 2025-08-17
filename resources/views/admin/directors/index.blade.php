@extends('admin.layouts.master')

@section('title')
    <title>Quản lý đạo diễn</title>
@endsection

@section('content')
    <section class="content-header">
        <ol class="breadcrumb">
            <li><a href="/admin"><i class="fa fa-home"></i>Trang chủ</a></li>
            <li class="active">Danh sách đạo diễn</li>
        </ol>
        <ul class="right-button">
            <li>
                <a href="{{ route('admin.directors.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Thêm đạo diễn
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
                                   class="form-control" placeholder="Tìm theo tên đạo diễn...">
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
                            <th>Phim</th>
                            <th>Cập nhật</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $index => $director)
                            <tr>
                                <td>{{ $data->firstItem() + $index }}</td>
                                <td>{{ $director->name }}</td>
                                <td>{{ $director->slug }}</td>
                                <td>{{ $director->movies_count ?? 0 }}</td>
                                <td>{{ $director->updated_at ? $director->updated_at->format('d/m/Y H:i') : '---' }}</td>
                                <td>
                                    <a href="{{ route('admin.directors.edit', $director->id) }}"
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
