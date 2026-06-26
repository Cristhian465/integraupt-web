<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FileController extends Controller
{
    private const MAX_SIZE_KB = 5120; // 5 MB
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:' . self::MAX_SIZE_KB,
        ]);

        $file = $request->file('file');
        $nombre = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/canal-media', $nombre);

        $url = url('storage/canal-media/' . $nombre);

        return response()->json(['url' => $url]);
    }
}
