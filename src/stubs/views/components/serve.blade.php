@php
    $enums = [];
    foreach (config('enums.serve') as $enumCls) {
        $enums[] = call_user_func([$enumCls, 'asServableEnum']);
    }
@endphp

<script>
    window.m2_ServedEnums = @js($enums);
</script>
