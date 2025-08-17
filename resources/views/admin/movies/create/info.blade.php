<div class="tab-pane active" id="tab-info">
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-solid">
                <div class="box-body">
                    {{-- Thông tin phim --}}
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Tiêu đề phim <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="VD: Fast & Furious" required>
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Slug</label>
                                <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="Tự động tạo nếu để trống">
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Hiển thị</label>
                                <select name="hidden" class="form-control select2">
                                    <option value="">-- Hiển thị --</option>
                                    <option value="0" {{ old('hidden') == '0' ? 'selected' : '' }}>Hiển thị</option>
                                    <option value="1" {{ old('hidden') == '1' ? 'selected' : '' }}>Ẩn</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Poster --}}
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Poster (Ảnh dọc)</label>
                                <input type="file" name="poster" class="form-control">
                            </div>
                        </div>

                        {{-- Thể loại --}}
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Thể loại</label>
                                <select name="genre_ids[]" class="form-control select2" multiple>
                                    @foreach ($genres as $genre)
                                        <option value="{{ $genre->id }}" {{ in_array($genre->id, old('genre_ids', [])) ? 'selected' : '' }}>
                                            {{ $genre->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Link m3u8 & Embed --}}
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Link m3u8</label>
                                <input type="text" name="m3u8" class="form-control" 
                                    value="{{ old('m3u8') }}" placeholder="Nhập link m3u8">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Link Embed</label>
                                <input type="text" name="embed" class="form-control" 
                                    value="{{ old('embed') }}" placeholder="Nhập link embed">
                            </div>
                        </div>
                    </div>

                    {{-- Mô tả --}}
                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Tóm tắt nội dung phim">{{ old('description') }}</textarea>
                    </div>

                    {{-- Meta SEO --}}
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Meta Title</label>
                                <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title') }}">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Meta Description</label>
                                <textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
