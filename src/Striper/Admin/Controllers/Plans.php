<?php 
namespace Striper\Admin\Controllers;

class Plans extends \Admin\Controllers\BaseAuth 
{
    use \Dsc\Traits\Controllers\AdminList;
    use \Dsc\Traits\Controllers\SupportPreview;
    
    protected $list_route = '/admin/striper/plans';

    protected function getModel($name = 'plans')
    {
        $model = null;
        switch( $name ) {
        	case 'plans' :
		        $model = new \Striper\Models\Plans;
        		break;
     
        }
        return $model;
    }
	
	public function index()
    {
        // when ACL is ready
        //$this->checkAccess( __CLASS__, __FUNCTION__ );
        
        $model = $this->getModel();
        $state = $model->populateState()->getState();
        $this->app->set('state', $state );
        
        $paginated = $model->paginate();
        $this->app->set('paginated', $paginated );
        
        /*$categories_db = (array) $this->getModel( "categories" )->getItems();
        $categories = array(
        	array( 'text' => 'All Categories', 'value' => ' ' ),
       		array( 'text' => '- Uncategorized -', 'value' => '--' ),
        );
        array_walk( $categories_db, function($cat) use(&$categories) {
        	$categories []= array(
        			'text' => $cat->title,
        			'value' => (string)$cat->slug,
        	);
        } );
        
        $this->app->set('categories', $categories );
        */
        
       /* $all_tags = array(
       		array( 'text' => 'All Tags', 'value' => ' ' ),
       		array( 'text' => '- Untagged -', 'value' => '--' ),
        );
        $tags = (array) $this->getModel()->getTags();
        array_walk( $tags, function($tag) use(&$all_tags) {
        	$all_tags []= array(
        			'text' => $tag,
        			'value' => $tag
        	);
        } );

        $this->app->set('all_tags', $all_tags );
        */
        $this->app->set('meta.title', 'Plans');
        $this->app->set( 'allow_preview', $this->canPreview( true ) );
        
        $view = \Dsc\System::instance()->get('theme');
        echo $view->render('Striper/Admin/Views::plans/list.php');
    }
}