@include('errors._enterprise', [
    'statusCode' => 403,
    'codeLabel' => __('errors.403_code'),
    'title' => __('errors.403_title'),
    'body' => config('app.debug') && $exception?->getMessage() ? $exception->getMessage() : __('errors.403_body'),
])
