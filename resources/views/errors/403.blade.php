@include('errors._enterprise', [
    'statusCode' => 403,
    'codeLabel' => __('errors.403_code'),
    'title' => __('errors.403_title'),
    'body' => __('errors.403_body'),
])
