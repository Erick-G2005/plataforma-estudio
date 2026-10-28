@props(['content' => ''])

@php
    // El HTML escrito dentro del texto se descarta y los enlaces peligrosos se bloquean.
    $html = \Illuminate\Support\Str::markdown((string) $content, [
        'html_input' => 'strip',
        'allow_unsafe_links' => false,
        'renderer' => ['soft_break' => "<br />\n"],
    ]);

    $classes = implode(' ', [
        'text-gray-700 leading-relaxed break-words',
        '[&_h1]:mt-6 [&_h1]:mb-3 [&_h1]:text-2xl [&_h1]:font-bold [&_h1]:text-gray-900',
        '[&_h2]:mt-6 [&_h2]:mb-2 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-gray-900',
        '[&_h3]:mt-4 [&_h3]:mb-2 [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:text-gray-900',
        '[&_p]:my-3',
        '[&_ul]:my-3 [&_ul]:list-disc [&_ul]:ps-6',
        '[&_ol]:my-3 [&_ol]:list-decimal [&_ol]:ps-6',
        '[&_li]:my-1',
        '[&_a]:text-indigo-600 [&_a]:underline',
        '[&_strong]:font-semibold [&_strong]:text-gray-900',
        '[&_blockquote]:my-4 [&_blockquote]:border-s-4 [&_blockquote]:border-indigo-200 [&_blockquote]:ps-4 [&_blockquote]:italic [&_blockquote]:text-gray-600',
        '[&_code]:rounded [&_code]:bg-gray-100 [&_code]:px-1 [&_code]:text-sm',
        '[&_pre]:my-4 [&_pre]:overflow-x-auto [&_pre]:rounded-lg [&_pre]:bg-gray-900 [&_pre]:p-4 [&_pre]:text-gray-100',
        '[&_pre_code]:bg-transparent [&_pre_code]:p-0 [&_pre_code]:text-gray-100',
        '[&_hr]:my-6 [&_img]:max-w-full [&_img]:rounded-lg',
    ]);
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {!! $html !!}
</div>
