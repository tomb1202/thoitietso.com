<script>
    $('.select2').select2();
</script>

@if (isset($movie))
    <script>
        let episodeIndex = {{ isset($episodeList) ? count($episodeList) : 0 }};

        $('#btn-add-episode').on('click', function() {
            const html = `
            <div class="episode-item row align-items-center mb-3" data-new="1">
                <input type="hidden" name="episodes[${episodeIndex}][id]" value="">

                <div class="col-md-2">
                    <input type="text" name="episodes[${episodeIndex}][number]" class="form-control form-control-sm" placeholder="Số tập">
                </div>

                <div class="col-md-3">
                    <input type="text" name="episodes[${episodeIndex}][title]" class="form-control form-control-sm" placeholder="Tiêu đề">
                </div>

                <div class="col-md-5">
                    <input type="text" name="episodes[${episodeIndex}][sources][0][video]" class="form-control form-control-sm mb-1" placeholder="M3U8 Link">
                    <input type="hidden" name="episodes[${episodeIndex}][sources][0][type]" value="m3u8">
                    <input type="hidden" name="episodes[${episodeIndex}][sources][0][label]" value="Default">

                    <input type="text" name="episodes[${episodeIndex}][sources][1][video]" class="form-control form-control-sm" placeholder="Embed Link">
                    <input type="hidden" name="episodes[${episodeIndex}][sources][1][type]" value="embed">
                    <input type="hidden" name="episodes[${episodeIndex}][sources][1][label]" value="Default">
                </div>

                <div class="col-md-2 text-right">
                    <button type="button" class="btn btn-danger btn-sm btn-remove-episode"><i class="fa fa-trash"></i></button>
                </div>
            </div>
        `;

            $('#new-episodes-list').append(html);
            episodeIndex++;
        });

        $(document).on('click', '.btn-remove-episode', function() {
            const $episodeItem = $(this).closest('.episode-item');
            const episodeId = $episodeItem.find('input[name$="[id]"]').val();

            if (episodeId) {
                $('#deleted-episodes-container').append(
                    `<input type="hidden" name="deleted_episode_ids[]" value="${episodeId}">`);
            }

            $episodeItem.remove();
        });
    </script>
@else
    <script>
        let episodeIndex = 0;

        $('#btn-add-episode').on('click', function() {
            const html = `
            <div class="episode-item row align-items-center mb-3" data-new="1">
                <input type="hidden" name="episodes[${episodeIndex}][id]" value="">

                <div class="col-md-2">
                    <input type="text" name="episodes[${episodeIndex}][number]" class="form-control form-control-sm" placeholder="Số tập">
                </div>

                <div class="col-md-3">
                    <input type="text" name="episodes[${episodeIndex}][title]" class="form-control form-control-sm" placeholder="Tiêu đề">
                </div>

                <div class="col-md-5">
                    <input type="text" name="episodes[${episodeIndex}][sources][0][video]" class="form-control form-control-sm mb-1" placeholder="M3U8 Link">
                    <input type="hidden" name="episodes[${episodeIndex}][sources][0][type]" value="m3u8">
                    <input type="hidden" name="episodes[${episodeIndex}][sources][0][label]" value="Default">

                    <input type="text" name="episodes[${episodeIndex}][sources][1][video]" class="form-control form-control-sm" placeholder="Embed Link">
                    <input type="hidden" name="episodes[${episodeIndex}][sources][1][type]" value="embed">
                    <input type="hidden" name="episodes[${episodeIndex}][sources][1][label]" value="Default">
                </div>

                <div class="col-md-2 text-right">
                    <button type="button" class="btn btn-danger btn-sm btn-remove-episode"><i class="fa fa-trash"></i></button>
                </div>
            </div>
        `;

            $('#new-episodes-list').append(html);
            episodeIndex++;
        });

        // Xoá dòng tập (không cần deleted_episode_ids khi tạo)
        $(document).on('click', '.btn-remove-episode', function() {
            $(this).closest('.episode-item').remove();
        });
    </script>
@endif
