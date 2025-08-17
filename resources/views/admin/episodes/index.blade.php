@extends('admin.layouts.master')

@section('title')
    <title>Danh sách tập phim</title>
@endsection

@section('content')
    <section class="content-header">
        <ol class="breadcrumb">
            <li><a href="/admin"><i class="fa fa-home"></i>Trang chủ</a></li>
            <li class="active">Danh sách tập phim</li>
        </ol>
        <div class="clearfix"></div>
    </section>

    <section class="content">
        <div class="box box-solid">
            <div class="box-body">
                <div class="search">
                    <form action="" method="GET">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group clearfix">
                                    <input type="text" name="search" value="{{ request('search') }}"
                                        class="form-control" placeholder="Tìm theo tên phim...">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-success">Tìm kiếm</button>
                            </div>
                        </div>
                    </form>
                </div>

                <table class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tên phim</th>
                            <th>Số tập</th>
                            <th>Tiêu đề</th>
                            <th>Slug</th>
                            <th>Cập nhật</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $index => $episode)
                            <tr>
                                <td>{{ $data->firstItem() + $index }}</td>
                                <td>
                                    @if ($episode->movie)
                                        <a href="{{ route('admin.movies.edit', $episode->movie->id) }}" target="_blank">
                                            {{ $episode->movie->title }}
                                        </a>
                                    @else
                                        <span class="text-muted">[Không tìm thấy]</span>
                                    @endif
                                </td>
                                <td>
                                    <span>
                                        {{ $episode->number ?? '---' }}
                                    </span>
                                    <span style="margin-left: 10px">
                                        <a href="#" class="text-primary small watch-episode"
                                            data-id="{{ $episode->id }}">
                                            <i class="fa fa-play-circle"></i> Xem tập {{$episode->number}}
                                        </a>
                                    </span>
                                </td>

                                <td>{{ $episode->title ?? '---' }}</td>
                                <td>{{ $episode->slug }}</td>
                                <td>{{ $episode->updated_at ? $episode->updated_at->format('d/m/Y H:i') : '---' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {!! $data->appends(request()->input())->links('admin.widgets.default') !!}
            </div>
        </div>
    </section>
@endsection
