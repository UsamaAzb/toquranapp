<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Document Viewer Providers
    |--------------------------------------------------------------------------
    |
    | Google is the launch default for PDFs because mobile/PWA native PDF
    | rendering leaves the app. Native remains a one-step rollback for desktop
    | debugging. Office documents use Microsoft Office Online by default.
    |
    */

    'pdf_provider' => env('DOCUMENT_VIEWER_PDF_PROVIDER', 'google'),

    'office_provider' => env('DOCUMENT_VIEWER_OFFICE_PROVIDER', 'microsoft'),
];
