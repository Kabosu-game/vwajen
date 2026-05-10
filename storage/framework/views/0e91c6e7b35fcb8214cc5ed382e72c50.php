<div
    <?php echo e($attributes
            ->merge([
                'id' => $getId(),
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)); ?>

>
    <?php echo e($getChildComponentContainer()); ?>

</div>
<?php /**PATH /opt/lampp/htdocs/all/vwajen_app/vwajen_api/vendor/filament/forms/resources/views/components/group.blade.php ENDPATH**/ ?>