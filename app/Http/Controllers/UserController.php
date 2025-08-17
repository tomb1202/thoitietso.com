<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Movie;
use App\Models\User;
use App\Models\UserVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class UserController extends Controller
{
    public function ajaxLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $html = View::make('site.widgets.user-slot', [
                'user' => Auth::user()
            ])->render();

            return response()->json([
                'status' => true,
                'html' => $html,
                'is_login' => true
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Incorrect email or password.'
        ]);
    }

    public function ajaxRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        $html = View::make('site.widgets.user-slot', [
            'user' => $user
        ])->render();

        return response()->json([
            'status' => true,
            'html' => $html,
            'is_login' => true
        ]);
    }

    public function ajaxUpdateProfile(Request $request)
    {
        $userId = Auth::user()->id;
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Please log in again.'
            ]);
        }

        if ($request->filled('password') && !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Current password is incorrect.'
            ]);
        }

        $user->name = $request->input('name', $user->name);

        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return response()->json(['status' => true]);
    }

    public function profile()
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'You must be logged in to access this page.');
        }

        $user = Auth::user();

        return view('site.user.profile', [
            'user' => $user
        ]);
    }

    public function favorite()
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'You must be logged in to access this page.');
        }

        $user = Auth::user();

        // Lấy danh sách movie_id yêu thích
        $movieIds = Favorite::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->pluck('movie_id')
            ->toArray();

        // Nếu không có favorite → trả empty paginate
        if (empty($movieIds)) {
            $movies = Movie::whereRaw('0=1')->paginate(20);
        } else {
            // Lấy movie theo favorite
            $movies = Movie::whereIn('id', $movieIds)
                ->where('hidden', 0)
                ->orderByRaw('FIELD(id, ' . implode(',', $movieIds) . ')')
                ->paginate(20);
        }

        return view('site.user.favorite', [
            'movies' => $movies
        ]);
    }

    public function ajaxFavorite(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => false,
                'message' => 'You must be logged in.',
                'html' => '' // Trả về html rỗng nếu chưa login
            ]);
        }

        $user = Auth::user();
        $movieId = $request->input('movie_id');

        // Validate movie_id
        if (empty($movieId) || !Movie::where('id', $movieId)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid movie.',
                'html' => ''
            ]);
        }

        // Check if already favorited
        $favorite = Favorite::where('user_id', $user->id)
            ->where('movie_id', $movieId)
            ->first();

        if ($favorite) {
            // If already favorited → remove
            $favorite->delete();

            return response()->json([
                'status' => true,
                'message' => 'Removed from favorites.',
                'html' => ''
            ]);
        } else {
            // Add to favorite
            Favorite::create([
                'user_id' => $user->id,
                'movie_id' => $movieId
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Added to favorites.',
                'html' => ''
            ]);
        }
    }

    public function ajaxRemoveFavorite(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => false,
                'message' => 'You must be logged in.'
            ]);
        }

        $user = Auth::user();
        $movieId = $request->input('movie_id');

        // Validate movie_id
        if (empty($movieId)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid movie.'
            ]);
        }

        // Delete favorite
        Favorite::where('user_id', $user->id)
            ->where('movie_id', $movieId)
            ->delete();

        return response()->json([
            'status' => true,
            'message' => 'Removed from favorites.'
        ]);
    }

    public function ajaxVoteMovie(Request $request, $movie_id)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => false,
                'message' => 'You must be logged in.',
                'html' => ''
            ]);
        }

        $user = Auth::user();
        $state = $request->input('state'); // 1 = like, 0 = dislike

        if (!Movie::where('id', $movie_id)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid movie.',
                'html' => ''
            ]);
        }

        if (!in_array($state, [0, 1])) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid vote.',
                'html' => ''
            ]);
        }

        // Convert state to vote value: 1 = like, -1 = dislike
        $vote_value = ($state == 1) ? 1 : -1;

        // Upsert vote
        UserVote::updateOrCreate(
            [
                'user_id' => $user->id,
                'movie_id' => $movie_id
            ],
            [
                'vote' => $vote_value
            ]
        );

        // Tính lại điểm (giống bên show())
        $likes = UserVote::where('movie_id', $movie_id)->where('vote', 1)->count();
        $dislikes = UserVote::where('movie_id', $movie_id)->where('vote', -1)->count();

        $totalVotes = $likes + $dislikes;

        if ($totalVotes > 0) {
            $likeRatio = $likes / $totalVotes;
            $score = round($likeRatio * 5, 1);
            $progress = $likeRatio * 100;
        } else {
            $score = 0.0;
            $progress = 0;
        }

        // Render HTML block-rating
        $html = '
    <div class="rr-mark"><span>' . $score . '</span>/ ' . $totalVotes . ' voted</div>
    <div class="progress">
        <div class="progress-bar bg-success" role="progressbar" style="width: ' . $progress . '%;" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    ';

        return response()->json([
            'status' => true,
            'message' => 'Vote recorded.',
            'html' => $html
        ]);
    }
}
