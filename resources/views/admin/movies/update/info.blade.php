<div class="tab-pane active" id="tab-info">
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-solid">
                <div class="box-body">
                    {{-- Thông tin phim --}}
                    <div class="row">
                        {{-- Tiêu đề --}}
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Tiêu đề phim <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control"
                                    value="{{ old('title', $movie->title ?? '') }}" placeholder="VD: Fast & Furious" required>
                            </div>
                        </div>

                        {{-- Slug --}}
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Slug</label>
                                <input type="text" name="slug" class="form-control"
                                    value="{{ old('slug', $movie->slug ?? '') }}" placeholder="Tự động tạo nếu để trống">
                            </div>
                        </div>

                        {{-- Hiển thị --}}
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Hiển thị</label>
                                <select name="hidden" class="form-control select2">
                                    <option value="">-- Hiển thị --</option>
                                    <option value="0" {{ old('hidden', $movie->hidden ?? 0) == 0 ? 'selected' : '' }}>Hiển thị</option>
                                    <option value="1" {{ old('hidden', $movie->hidden ?? 1) == 1 ? 'selected' : '' }}>Ẩn</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Poster --}}
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Poster (Ảnh dọc)
                                    @if(!empty($movie->poster))
                                        <span><a target="_blank" href="{{ asset('storage/images/posters/' . $movie->poster) }}">Xem ảnh</a></span>
                                    @endif
                                </label>
                                <input type="file" name="poster" class="form-control">
                            </div>
                        </div>

                        {{-- Thể loại --}}
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Thể loại</label>
                                <select name="genre_ids[]" class="form-control select2" multiple>
                                    @foreach ($genres as $genre)
                                        <option value="{{ $genre->id }}"
                                            {{ in_array($genre->id, old('genre_ids', $movie->genres->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $genre->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Nguồn Video --}}
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Link m3u8</label>
                                <input type="text" name="m3u8" class="form-control"
                                    value="{{ old('m3u8', optional($movie->sources->where('type', 'm3u8')->first())->video) }}"
                                    placeholder="Nhập link m3u8">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Link Embed</label>
                                <input type="text" name="embed" class="form-control"
                                    value="{{ old('embed', optional($movie->sources->where('type', 'embed')->first())->video) }}"
                                    placeholder="Nhập link embed">
                            </div>
                        </div>
                    </div>

                    {{-- Mô tả --}}
                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Tóm tắt nội dung phim">{!! old('description', $movie->description ?? '') !!}</textarea>
                    </div>

                    {{-- Meta SEO --}}
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Meta Title</label>
                                <input type="text" name="meta_title" class="form-control"
                                    value="{{ old('meta_title', $movie->meta_title ?? '') }}">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Meta Description</label>
                                <textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description', $movie->meta_description ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
