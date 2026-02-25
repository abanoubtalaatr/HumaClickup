<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pull Request required track IDs
    |--------------------------------------------------------------------------
    |
    | Only guests in these tracks are required to submit pull requests.
    | Set to a list of track IDs (e.g. [2, 4, 12]) for this workspace.
    | Leave empty [] to use name-based fallback (backend, frontend, mobile).
    |
    */

    // Set track IDs that require PR (e.g. [2, 4, 12]). Empty = use name-based fallback (backend, frontend, mobile).
    'track_ids' => env('PULL_REQUEST_TRACK_IDS')
        ? array_filter(array_map('intval', explode(',', env('PULL_REQUEST_TRACK_IDS'))))
        : [2, 4, 12, 10, 9, 8],

];
