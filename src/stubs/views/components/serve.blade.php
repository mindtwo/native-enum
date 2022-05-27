@php
    $enums = [];
    foreach (config('enums.serve') as $enumCls) {
        $name = (new \ReflectionClass($enumCls))->getShortName();
        $enums[$name] = call_user_func([$enumCls, 'asServableEnum']);
    }
@endphp

<script>
    window.m2_ServedEnums = @js($enums);
</script>
