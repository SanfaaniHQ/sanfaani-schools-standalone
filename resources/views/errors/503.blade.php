@include('errors._enterprise', [
    'statusCode' => 503,
    'codeLabel' => __('errors.503_code'),
    'title' => __('errors.503_title'),
    'body' => __('errors.503_body'),
])
