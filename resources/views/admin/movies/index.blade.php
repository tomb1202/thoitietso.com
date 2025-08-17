@extends('admin.layouts.master')

@section('title')
    <title>
        Quản lý phim</title>

    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">

    <style>
        .poster-wrapper {
            position: relative;
            display: inline-block;
        }

        .poster-wrapper .change-text {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            text-align: center;
            font-size: 12px;
            padding: 2px 0;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            cursor: pointer;
        }

        .poster-wrapper:hover .change-text {
            opacity: 1;
        }

        tbody tr td {
            vertical-align: middle !important;
        }
    </style>
@endsection

@section('content')
    <section class="content-header">
        <ol class="breadcrumb">
            <li><a href="/admin"><i class="fa fa-home"></i>Trang chủ</a></li>
            <li class="active">Danh sách phim</li>
        </ol>
        <ul class="right-button">
            <li><a href="{{ route('admin.movies.create') }}" class="btn btn-block btn-primary">
                    <i class="fa fa-plus mr-1" aria-hidden="true"></i>Thêm mới</a>
            </li>
            <li>
                <button class="btn btn-danger btn-block btn-delete-multiple btn-sm" disabled>
                    <i class="fa fa-trash"></i> Xóa đã chọn <span class="selected-count">(0)</span>
                </button>
            </li>
        </ul>
        <div class="clearfix"></div>
    </section>

    <section class="content">
        <div class="box box-solid">
            <div class="box-body">
                <div class="search">
                    <form action="" method="GET">
                        <div class="row">
                            <div class="col-sm-3">

                            </div>
                            <div class="col-sm-2">
                                <div class="form-group clearfix">
                                    <select name="genre_id" class="form-control">
                                        <option value="">-- Thể loại</option>
                                        @foreach ($genres as $genre)
                                            <option value="{{ $genre->id }}" {{$request->genre_id == $genre->id ? 'selected' : ''}}>{{ $genre->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group clearfix">
                                    <select name="hidden" class="form-control">
                                        <option value="">-- Trạng thái --</option>
                                        <option value="0" {{ request('hidden') == 0 ? 'selected' : '' }}>Đang
                                            chiếu
                                        </option>
                                        <option value="1" {{ request('hidden') == 1 ? 'selected' : '' }}>
                                            Ẩn
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="form-group clearfix">
                                    <input type="text" name="search" value="{{ request('search') }}"
                                        class="form-control" placeholder="Tìm theo tiêu đề...">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-success">Tìm kiếm</button>
                            </div>
                        </div>
                    </form>
                </div>

                <table id="example2" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>#</th>
                            <th>Poster</th>
                            <th>Tiêu đề</th>
                            <th>Loại</th>
                            <th>Trạng thái</th>
                            <th>Cập nhật</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $index => $movie)
                            <tr class="tr-{{ $movie->id }}">
                                <td><input type="checkbox" class="select-item" value="{{ $movie->id }}"></td>
                                <td>{{ $data->firstItem() + $index }}</td>

                                <td style="width:100px">
                                    <div class="poster-wrapper text-center position-relative"
                                        style="display: inline-block;">
                                        <!-- Input file hidden -->
                                        <input type="file" class="change-poster-input" data-id="{{ $movie->id }}"
                                            accept="image/*" style="display:none;">

                                        <!-- Poster + Overlay -->
                                        <a href="javascript:void(0)" class="change-poster-trigger d-block"
                                            data-id="{{ $movie->id }}">
                                            <img src="{{ $movie->poster ? asset('storage/images/posters/' . $movie->poster) : 'https://via.placeholder.com/40x60?text=No+Img' }}"
                                                class="poster-img-{{ $movie->id }}"
                                                style="width: 40px; height: 60px; object-fit: cover; display:block;">
                                            <span class="change-text">Đổi</span>
                                        </a>
                                    </div>
                                    <p>
                                        <small>
                                            <a target="_blank" class="view-poster-link-{{ $movie->id }}"
                                                href="{{ $movie->poster ? asset('storage/images/posters/' . $movie->poster) : 'https://via.placeholder.com/40x60?text=No+Img' }}">
                                                Xem ảnh
                                            </a>
                                        </small>
                                    </p>
                                </td>


                                <td>
                                    <span>
                                        {{ \Illuminate\Support\Str::limit($movie->title, 60) }}
                                    </span>

                                    <span style="margin-left: 10px">
                                        <a target="_blank" href="{{ route('movie.watch', ['slug' => $movie->slug]) }}"
                                            class="text-primary small watch-movie" data-id="{{ $movie->id }}">
                                            <i class="fa fa-play-circle"></i> Xem phim
                                        </a>
                                    </span>

                                </td>

                                <td>
                                    @php
                                        $genres = $movie->genres;
                                        $displayGenres = $genres->take(3);
                                    @endphp

                                    @foreach ($displayGenres as $index => $genre)
                                        <a href="{{ route('site.genre', $genre->slug) }}">{{ $genre->name }}</a>
                                        @if ($index < $displayGenres->count() - 1)
                                            ,
                                        @endif
                                    @endforeach

                                    @if ($genres->count() > 3)
                                        ...
                                    @endif
                                </td>



                                <td>
                                    <input type="checkbox" class="adv-active" data-id="{{ $movie->id }}"
                                        {{ $movie->hidden == 0 ? 'checked' : '' }} data-toggle="toggle" data-size="xs"
                                        data-onstyle="success" data-offstyle="danger" data-on="Bật" data-off="Tắt"
                                        data-width="50" data-heigth="10">
                                </td>
                                <td>{{ $movie->updated_at ? $movie->updated_at->format('d/m/Y H:i') : '---' }}</td>

                                <td>
                                    <a href="{{ route('admin.movies.edit', $movie->id) }}" class="btn btn-sm btn-primary"
                                        title="Chỉnh sửa"><i class="fa fa-pencil"></i></a>
                                    <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $movie->id }}"
                                        title="Xóa"><i class="fa fa-times"></i></button>
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

@section('script')
    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
    <script>
        $(document).on('click', '.btn-delete', function() {
            let id = $(this).data('id');
            if (!confirm('Bạn có chắc chắn muốn xóa phim này?')) return;

            $.ajax({
                url: "{{ route('admin.movies.delete') }}",
                type: "DELETE",
                data: {
                    id: id,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        $('.tr-' + id).fadeOut();
                    } else {
                        alert('Xóa thất bại: ' + response.message);
                    }
                },
                error: function() {
                    alert('Có lỗi xảy ra khi xóa.');
                }
            });
        });

        $(document).ready(function() {
            let selectedItems = [];

            $('#select-all').on('change', function() {
                $('.select-item').prop('checked', $(this).prop('checked')).trigger('change');
            });

            $('.select-item').on('change', function() {
                selectedItems = $('.select-item:checked').map(function() {
                    return $(this).val();
                }).get();

                $('.selected-count').text(`(${selectedItems.length})`);
                $('.btn-delete-multiple').prop('disabled', selectedItems.length === 0);
            });

            $('.btn-delete-multiple').on('click', function() {
                if (!confirm(`Bạn có chắc chắn muốn xóa ${selectedItems.length} phim đã chọn?`)) return;

                $.ajax({
                    url: "{{ route('admin.movies.delete-multiple') }}",
                    type: "DELETE",
                    data: {
                        ids: selectedItems,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            selectedItems.forEach(id => $(`.tr-${id}`).fadeOut());
                            $('#select-all').prop('checked', false);
                            $('.selected-count').text('(0)');
                            $('.btn-delete-multiple').prop('disabled', true);
                        } else {
                            alert('Xóa nhiều thất bại!');
                        }
                    },
                    error: function() {
                        alert('Có lỗi xảy ra khi xóa.');
                    }
                });
            });
        });
    </script>

    <script>
        $(document).on('change', '.adv-active', function() {
            const checkbox = $(this);
            const id = checkbox.data('id');
            const hidden = checkbox.prop('checked');

            $.ajax({
                url: '{{ route('admin.movies.active') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    hidden: hidden
                },
                success: function(res) {
                    if (!res.error) {
                        toastr.success(res.message);
                    } else {
                        toastr.error(res.message);
                        checkbox.bootstrapToggle('toggle');
                    }
                },
                error: function() {
                    toastr.error('Đã xảy ra lỗi.');
                    checkbox.bootstrapToggle('toggle');
                }
            });
        });
    </script>

    <script>
        $(document).on('click', '.change-poster-trigger', function() {
            let id = $(this).data('id');
            $(`.change-poster-input[data-id="${id}"]`).click();
        });

        $(document).on('change', '.change-poster-input', function() {
            let fileInput = $(this);
            let id = fileInput.data('id');
            let file = fileInput[0].files[0];

            if (!file) return;

            let formData = new FormData();
            formData.append('poster', file);
            formData.append('id', id);
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: "{{ route('admin.movies.change-poster') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        let newUrl = res.poster_url + '?' + new Date().getTime();

                        // Cập nhật hình ảnh mới
                        $(`.poster-img-${id}`).attr('src', newUrl);

                        // Cập nhật link "Xem ảnh"
                        $(`.view-poster-link-${id}`).attr('href', newUrl);

                        toastr.success('Đã đổi poster thành công!');
                    } else {
                        toastr.error(res.message || 'Đổi poster thất bại!');
                    }
                },
                error: function() {
                    toastr.error('Có lỗi xảy ra khi upload poster.');
                }
            });
        });
    </script>
@endsection
