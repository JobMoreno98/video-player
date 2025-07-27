<?php

namespace App\Http\Controllers;

use App\Models\Videos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class VideosController extends Controller
{
    public function index()
    {
        $videos = Videos::paginate(10);
        return view('videos.index', compact('videos'));
    }

    public function create()
    {
        return view('videos.create');
    }
    public function show($id)
    {
        $video = Videos::findOrfail($id);
        $signedUrl = URL::temporarySignedRoute('video.playlist', now()->addMinutes(10), ['playlist' =>  $video->uiid]);
        return view('videos.ver', compact('signedUrl','video'));
    }
}
