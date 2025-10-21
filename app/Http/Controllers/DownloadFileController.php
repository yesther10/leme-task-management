<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadFileController extends Controller
{
    public function download($filename)
    {
        $path = 'private/project_attachments/98CwqV5ky23HpCumFmjI5N1TF1LrwDVRvX3t1y19.pdf' ;///' . $filename;
        dd($path, 'project_attachments');
        if (Storage::exists($path)) {
            return Storage::download($path);
        }
        abort(404);
    }
}
