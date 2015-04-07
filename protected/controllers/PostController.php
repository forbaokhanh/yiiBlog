<?php

class PostController extends Controller
{

	private $_model;
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
	    return array(
	        array('allow',  // allow all users to perform 'list' and 'show' actions
	            'actions'=>array('index', 'view'),
	            'users'=>array('*'),
	        ),
	        array('allow', // allow authenticated users to perform any action
	            'users'=>array('@'), //@ authenticated, * everything else
	        ),
	        array('deny',  // deny all users
	            'users'=>array('*'),
	        ),
	    );
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView()
	{
	    $post=$this->loadModel();
	    $this->render('view',array(
	        'model'=>$post,
	        'comment'=>$comment,
	    ));
	}
	 
	protected function newComment($post)
	{
	    $comment=new Comment;
	    if(isset($_POST['Comment']))
	    {
	        $comment->attributes=$_POST['Comment'];
	        if($post->addComment($comment))
	        {
	            if($comment->status==Comment::STATUS_PENDING)
	                Yii::app()->user->setFlash('commentSubmitted','Thank you for your comment. Your comment will be posted once it is approved.');
	            $this->refresh();
	        }
	    }
	    return $comment;
	}


	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Post;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Post']))
		{
			$model->attributes=$_POST['Post'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Post']))
		{
			$model->attributes=$_POST['Post'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete()
	{
	    if(Yii::app()->request->isPostRequest) // Only allowing deletions via post request
	    {
	        // we only allow deletion via POST request
	        $this->loadModel()->delete();
	
	        if(!isset($_GET['ajax'])) // only reload if it was not an ajax call
	            $this->redirect(array('index'));
	    }
	    else
	        throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	protected function afterDelete()
	{
	    parent::afterDelete(); // doing the afterDelete action of the class it inherits from
	    Comment::model()->deleteAll('post_id='.$this->id); // deletes the comments for that post
	    Tag::model()->updateFrequency($this->tags, ''); // updates the tags to nothing
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
	    $criteria=new CDbCriteria(array(
	        'condition'=>'status='.Post::STATUS_PUBLISHED,
	        'order'=>'update_time DESC',
	        'with'=>'commentCount',
	    ));
	    if(isset($_GET['tag']))
	        $criteria->addSearchCondition('tags',$_GET['tag']);
	 
	    $dataProvider=new CActiveDataProvider('Post', array(
	        'pagination'=>array(
	            'pageSize'=>5,
	        ),
	        'criteria'=>$criteria,
	    ));
	 
	    $this->render('index',array(
	        'dataProvider'=>$dataProvider,
	    ));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Post('search'); // creates a POst model under search scenario.
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Post']))
			$model->attributes=$_GET['Post']; // collectin the search condition that a user specifies(user supplied data)

		$this->render('admin',array( // finally render this information to the admin view
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Post the loaded model
	 * @throws CHttpException
	 */
	public function loadModel()
	{
	    if($this->_model===null)
	    {
	        if(isset($_GET['id'])) // returning the SQL statement
	        {
	        	// If the user is authenticated and is a Guest only allow him to view Published and archived posts.
	            if(Yii::app()->user->isGuest)
	            	// This syntax is a bit confusing. But you are setting the condition varable to some constant
	                $condition='status='.Post::STATUS_PUBLISHED
	                    .' OR status='.Post::STATUS_ARCHIVED;
	            else
	            	// or nothing
	                $condition='';
	            // Finding a post by a Pk? $_GET['id'] returns a primary key? Finds a single active record with a specified primary key.
	            $this->_model=Post::model()->findByPk($_GET['id'], $condition); //findBySql(string $sql, array $params=array ( ))
	        }
	        if($this->_model===null)
	            throw new CHttpException(404,'The requested page does not exist.');
	    }
	    return $this->_model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Post $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='post-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
