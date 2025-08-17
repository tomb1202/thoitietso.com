<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Movie\StoreMovieRequest;
use App\Http\Requests\Movie\UpdateMovieRequest;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Movie;
use App\Services\MovieService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;


class MovieController extends Controller
{

    protected $movieService;

    public function __construct(MovieService $movieService)
    {
        $this->movieService = $movieService;
    }

    public function index(Request $request)
    {
        $countries = Country::all();

        $query = Movie::query();

        if ($request->has('country_id') && !empty($request->country_id)) {
            $query->whereHas('countries', function ($q) use ($request) {
                $q->where('country_id', $request->country_id);
            });
        }

        if ($request->has('genre_id') && !empty($request->genre_id)) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genre_id', $request->genre_id);
            });
        }

        if ($request->has('hidden') && $request->hidden !== '') {
            $query->where('hidden', (int)$request->hidden);
        }

        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(title_en) LIKE ?', ["%{$search}%"]);
            });
        }

        $data = $query->with(['genres'])->orderByDesc('id')->paginate(30);

        $genres = Genre::where(['hidden' => 0, 'is_main' => 1])->get();

        return view('admin.movies.index', [
            'data' => $data,
            'request' => $request,
            'countries' => $countries,
            'genres' => $genres
        ]);
    }


    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json(['success' => false, 'message' => 'Movie not found']);
        }

        $movie->delete();
        return response()->json(['success' => true]);
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        Movie::whereIn('id', $ids)->delete();

        return response()->json(['success' => true]);
    }

    public function create()
    {
        $genres     = Genre::orderBy('name')->get();

        return view('admin.movies.form', [
            'genres' => $genres,
        ]);
    }

    public function store(StoreMovieRequest $request)
    {
        $data = $request->validated();

        // Upload Poster
        if ($request->hasFile('poster')) {
            $data['poster'] = uploadImageLocal($request->file('poster'), 'poster-' . time(), false);
        }

        // Sinh code phim
        $data['code'] = $this->generateRandomCode();

        // Đẩy nguyên data từ form vào service
        $movie = $this->movieService->storeMovie($data);

        if (!$movie) {
            return redirect()->back()->with('error', 'Tạo phim thất bại.');
        }

        return redirect()->route('admin.movies.edit', $movie->id)->with('success', 'Tạo phim thành công!');
    }

    public function edit($id)
    {
        $movie = Movie::with(['genres', 'sources'])->findOrFail($id);

        $genres = Genre::orderBy('name')->get();

        return view('admin.movies.form', [
            'movie' => $movie,
            'genres' => $genres,
        ]);
    }


    public function update(UpdateMovieRequest $request, $id)
    {
        $data = $request->validated();

        // Upload poster (ảnh dọc)
        if ($request->hasFile('poster')) {
            $file = $request->file('poster');
            $posterName = uploadImageLocal($file, 'poster-' . $id, false);
            if (!$posterName) {
                return redirect()->back()->with('error', 'Upload poster thất bại.');
            }
            $data['poster'] = $posterName;
        }

        // Gọi service để cập nhật movie
        $updated = $this->movieService->updateMovie($id, $data);

        if (!$updated) {
            return redirect()->back()->with('error', 'Không tìm thấy phim hoặc cập nhật thất bại.');
        }

        return redirect()->back()->with('success', 'Cập nhật phim thành công!');
    }

    public function active(Request $request)
    {
        $movie = Movie::where(['id' => $request->id])->first();

        if (!isset($movie)) {
            return response()->json(['error' => true, 'message' => 'Movie does not exist']);
        }

        $movie->hidden = $request->hidden == "true" ? 0 : 1;
        $movie->save();

        return response()->json(['error' => false, 'message' => 'Updated successfully']);
    }

    public function changePoster(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:movies,id',
            'poster' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $movie = Movie::findOrFail($request->id);

        // Xóa poster cũ nếu có
        if ($movie->poster && Storage::exists('public/images/posters/' . $movie->poster)) {
            Storage::delete('public/images/posters/' . $movie->poster);
        }

        // Tạo tên file mới (luôn là webp)
        $posterName = uniqid('poster_') . '.webp';
        $path = storage_path('app/public/images/posters/' . $posterName);

        // Convert sang WebP và lưu
        $image = Image::make($request->file('poster'))->encode('webp', 90);
        $image->save($path);

        // Cập nhật DB, đồng thời reset timestamps về now()
        $movie->update([
            'poster' => $posterName,
            'thumbnail' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'poster_url' => asset('storage/images/posters/' . $posterName)
        ]);
    }
}
