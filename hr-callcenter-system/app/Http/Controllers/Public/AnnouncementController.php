<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::where('is_active', true)
            ->orderBy('publish_date', 'desc')
            ->paginate(9);

        return view('portal.announcements.index', compact('announcements'));
    }

    public function show($id)
    {
        $announcement = Announcement::where('is_active', true)->findOrFail($id);

        return view('portal.announcements.show', compact('announcement'));
    }
}
