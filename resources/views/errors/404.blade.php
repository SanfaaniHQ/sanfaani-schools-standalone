@include('errors._enterprise', [
    'statusCode' => 404,
    'codeLabel' => __('errors.404_code'),
    'title' => __('errors.404_title'),
    'body' => __('errors.404_body'),
])
