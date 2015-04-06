<!-- admin view -->

<?php
$this->breadcrumbs=array(
    'Manage Posts',
);
?>
<h1>Manage Posts</h1>
 <!-- CGridView allows to sort by a column and paginate -->
<?php $this->widget('zii.widgets.grid.CGridView', array(
    'dataProvider'=>$model->search(),
    'filter'=>$model,
    'columns'=>array(
        array(
            'name'=>'title',
            'type'=>'raw',
            'value'=>'CHtml::link(CHtml::encode($data->title), $data->url)'
        ),
        array(
            'name'=>'status',
            'value'=>'Lookup::item("PostStatus",$data->status)',
            'filter'=>Lookup::items('PostStatus'),
        ),
        array(
            'name'=>'create_time',
            'type'=>'datetime',
            'filter'=>false,
        ),
        array(
            'class'=>'CButtonColumn',
        ),
    ),
)); ?>
