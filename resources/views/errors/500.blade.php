@include('errors._enterprise', [
    'statusCode' => 500,
    'codeLabel' => __('errors.500_code'),
    'title' => __('errors.500_title'),
    'body' => __('errors.500_body'),
])
