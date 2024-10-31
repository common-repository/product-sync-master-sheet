<?php
namespace PSSG_Sync_Sheet\App\Service;

use PSSG_Sync_Sheet\App\Service\Standalone;

class Products{
    use Standalone;

    public $args = [];
    public $posts_per_page = 150;
    public $paged = 1;

    public $post_count;
    public $max_num_pages;
    public $found_posts;

    public $product_id;
    public $product;
    public $product_parent_id;
    public $product_type;
    public $product_data;
    public $products = [];

    public $stats = [];
    public $stats_counted;

    public $update_sheet_index = false;

    /**
     * New product Sheet range available or not
     * we will handle by this property
     * 
     * Default value is null or false
     *
     * @var bool|null
     */
    public $new_product_sheet_range_enable = true;

    /**
     * if null or empty the limit
     * we will convert to it unlimited
     *
     * @var integer
     */
    public $one_load_limit = 150;

    /**
     * Fonded counted rows on current 
     * query execution.
     */
    public $rows_count = 0;
    
    public $errors = [];

    public $columns = [];

    public $orderby = 'ID';
    public $order = 'ASC';


    /**
    
     *
     * @var integer
     */ 

    /**
     * Actually in get column count, we didn't counte 'product_id'
     * column, becuase, it's not availabel other there, 
     * we will set automatically this column 
     * 
     * specially at $this->get_columns_row() method
     * So chr(65+$this->get_columns_count()) 
     * here should 64, but I added it 65. 
     * 
     * karon $this->get_columns_count() ekhane ekta column already com dekhacche
     * 
     * 
     * //64+1+1 [auto already added colum is:product_id and product id]
     * //So I have added 2.
     * 
     * 
     * 
     * **************************
     * IMPORTANT UPDATE
     * **************************
     * I have added new method
     * $this->get_sheet_column_count();
     * and $this->get_sheet_last_letter();
     * Where I hadde 2 by default, sei jonno
     * base_char er sathe r extra 2 jog er dorkar nai.
     * 
     * @updated V1.0.0.11 
     * @author Saiful <codersaiful@gmail.com>
     */
    private $base_chr = 64; //A = 64 //actually two column is auto added.
    
    /**
     * I will keep here sheet's index specially for product's id.
     * suppose:
     * Sheet Inex: A:2
     * Product ID: 1234
     * my data will like following
     * [
     *   1234 => 2 //Actually id colun only will stay on A column of sheet, so I just keep row number on database.
     * ]
     *
     * @var array
     */
    public $sheet_index = [];

    /**
     * For database 
     * and I will keep it on wp_options table
     * with key name 'pssg_sheet_index_data'
     *
     * @var string
     */
    public $sheet_index_key = 'pssg_sheet_index_data';

    /**
     * Some property and Element item value can be
     * set using Construction. 
     * It's optional actually
     * 
     * ************************
     * supported property or items Example:
     * ************************
     * $this->posts_per_page = $setArgs['posts_per_page'] ?? $this->posts_per_page;
     * $this->paged = $setArgs['paged'] ?? $this->paged;
     * $this->order = $setArgs['order'] ?? $this->order;
     * $this->orderby = $setArgs['orderby'] ?? $this->orderby;
     * -------------------
     * @author Saiful Islam <codersaiful@gmail.com>
     * @param array $setArgs Such Emample: ['posts_per_page'=> 22, 'paged'=>2];
     */
    public function __construct($setArgs = [])
    {
            
        if( is_array( $setArgs ) ){
            $this->posts_per_page = $setArgs['posts_per_page'] ?? $this->posts_per_page;
            $this->paged = $setArgs['paged'] ?? $this->paged;
            $this->order = $setArgs['order'] ?? $this->order;
            $this->orderby = $setArgs['orderby'] ?? $this->orderby;
        }

        $this->posts_per_page = apply_filters( 'pssg_posts_per_page', $this->posts_per_page );
        $this->new_product_sheet_range_enable = apply_filters( 'pssg_new_product_sheet_range_enable', false );
        $this->one_load_limit = apply_filters( 'pssg_one_load_limit', $this->one_load_limit );
        
    }


    /**
     * When update to sheet,
     * then this time, we will call this method, because, 
     * we need it to update on sheet.
     * 
     *
     * @return void
     */
    public function getSheetRang()
    {
        $this->paged = apply_filters('pssg_args_paged_number',  $this->paged);

        

         
        $this->posts_per_page = apply_filters( 'pssg_posts_per_page', $this->posts_per_page );
        $l_letter = $this->get_sheet_last_letter();
        if($this->paged == 1){
            $f_num = 1;
            $l_num = $this->rows_count;
        }else{
            $f_num = 2 + ( ($this->paged - 1) * $this->posts_per_page);
            $l_num = $this->rows_count + $f_num - 1;
        }

        return "A$f_num:$l_letter".$l_num;
    }

    public function get_sheet_range_by_ids($ids = []){
        if( empty( $ids ) && ! is_array( $ids ) && ! isset( $ids[0] ) ) return;
        $last_id = end( $ids );
        $first_id = $ids[0];

        $sheet_index = $this->get_sheet_index();
        $l_letter = $this->get_sheet_last_letter();
        $f_num = $sheet_index[$first_id];
        $l_num = $sheet_index[$last_id];

        return "A$f_num:$l_letter".$l_num;
    }
    

    /**
     * Most Important is this method.
     * We will call this method at the last of 
     * all set items and value or property
     * 
     * **************
     * I have save SheetInex Number to wp_option to this method
     * why need this, actually if we want to update any one/single product,
     * then we need Goo Sheet number based on
     * 
     * ***************
     * 
     * ****** Used Sheet Index Save here ******
     * 
     * ########## 
     * IMPORTANT
     * ################# 
     * JOKHON $query_args thakbe, tokhon title column add hobe na.
     *
     * @param array $query_args to set new query args, added element for $args. such: [post__in = '1,2,3,4']
     * @return void
     */
    public function get_sheet_row( $query_args = [] )
    {
        
        $this->paged = apply_filters('pssg_args_paged_number',  $this->paged);
        
        $args = [
            'post_type'         => ['product', 'product_variation'],
            'posts_per_page'    => apply_filters( 'pssg_posts_per_page', $this->posts_per_page ),
            'paged'             => $this->paged,
            'order'             => $this->order,
            'orderby'           => $this->orderby

        ];
        if( ! empty( $query_args ) && is_array( $query_args ) ){
            $args = array_merge( $args, $query_args );
        }
        $this->args = apply_filters( 'pssg_products_args', $args, $this );
        $product_loop = new \WP_Query( $this->args );

        $this->post_count = $product_loop->post_count;
        $this->found_posts = $product_loop->found_posts;
        $this->max_num_pages = $product_loop->max_num_pages;
        
        if( $this->paged == 1 && ! isset( $query_args['post__in'] )  ){
            
            $this->rows_count++;
            $this->products[0] = $this->get_columns_row();
        }

        $sheet_index = $this->get_sheet_index();
        $s_index_serial = ( ($this->paged - 1) * $this->posts_per_page ) + 2;
        if (  $product_loop->have_posts() ) : while ($product_loop->have_posts()): $product_loop->the_post();
        global $product;
        $this->product = $product;
        $this->product_id = $product->get_id();
        $sheet_index[$this->product_id] = $s_index_serial;
        $this->product_type = $product->get_type();
        $this->product_parent_id = $product->get_parent_id();

        $this->product_data = $product->get_data();
        $elements = $this->organize_product_element();
        $this->products[] = $elements;

        $this->rows_count++;
        $s_index_serial++;
        endwhile;
        else:
        endif;
        wp_reset_postdata();
        wp_reset_query();
        
        if( $this->update_sheet_index && ! isset( $query_args['post__in'] ) ){
            $this->sheet_index = $sheet_index;
            $this->set_sheet_index( $this->sheet_index );
        }
        
        $this->stats_counted = true;
        return $this->products;
        
    }


    /**
     * Still not used in this Class,
     * I have just used it on steps/syncronize.php file
     *
     * @return array
     */
    public function get_stats(){
        if( ! $this->stats_counted){
            $this->get_sheet_row();
        }

        $this->stats['rows_count'] = $this->rows_count;
        $this->stats['found_posts'] = $this->found_posts;
        $this->stats['post_count'] = $this->post_count;
        $this->stats['max_num_pages'] = $this->max_num_pages;
        $this->stats['posts_per_page'] = $this->posts_per_page;

        return $this->stats;
    }

    public function get_sheet_multiple_row( $product_ids = [] )
    {
        if(empty( $product_ids ) || ! is_array( $product_ids ) ) return [];
        
        return $this->get_sheet_row(['post__in' => $product_ids]);
        
    }

    public function set_product( $product_id )
    {
        if( $this->product ) return;
        $this->product_id = $product_id;
        $this->product = wc_get_product( $product_id );
        $this->product_data = $this->product->get_data();

        $this->product_type = $this->product->get_type();
        $this->product_parent_id = $this->product->get_parent_id();

    }
    public function get_product_type( $product_id = 0 )
    {
        if( ! empty( $product_id ) ){
            $this->product_id = $product_id;
        }

        if( $this->product_id && $this->product_type ) return $this->product_type;

        $product = wc_get_product( $this->product_id );
        return $product->get_type();
    }

    
    public function get_product_title( $product_id = 0 )
    {
        if( ! empty( $product_id ) ){
            $this->product_id = $product_id;
        }
        
        $extra_title = '';
        if( $this->product_type == 'variation' ){
            $extra_title = $this->get_extra_variation_title();
        }

        $root_name = $this->product_data['name'] ?? '';
        return $root_name . $extra_title;

    }

    /**
     * This is Specially for Variation's Extra Title like - Blue, Red, Green
     * specially for gatter than two attribtues
     *
     * @return string|null
     */
    public function get_extra_variation_title(){

        if( empty( $this->product_id ) || empty( $this->product_type ) || $this->product_type !== 'variation' ) return;
        $attribtues = $this->product_data['attributes'] ?? [];
        if( ! is_array( $attribtues ) ) return;
        if( count( $attribtues ) <= 2 ) return;
        $title = ' - ';
        $title .= implode(', ', $attribtues);

        return ucwords($title);
    }


    /**
     * required property for this method is
     * $this->product_data 
     * for this product_data need = $product variable;
     *
     * @param [type] $product_id
     * @return array
     */
    public function get_sheet_row_by_product_id( $product_id )
    {
        $this->product_id = $product_id;
        $product = wc_get_product( $product_id );
        $this->product_data = $product->get_data();
        $this->products[] = $this->organize_product_element();
        return $this->products;
    }

    
    public function set_sheet_index_by_product_id( $product_id, $index_number )
    {
        $this->sheet_index = $this->get_sheet_index();
        $this->sheet_index[$product_id] = $index_number;
        update_option( $this->sheet_index_key, $this->sheet_index );
    }

    /**
     * Confusion:
     * When we didn't check here prev sheet_index
     * actually on get_sheet_row() method,
     * we checked and added new
     * 
     * User add param 1 of full array
     * to set sheet_index to database
     *
     * @param array $full_index_array
     * @return void
     */
    public function set_sheet_index( $full_index_array = [] )
    {
        //by default faka thake, jodi pai, mane $this->get_sheet_row() call kora hoyeche, and data ache. so I will jut return it.
        if( ! empty($this->sheet_index) && is_array( $this->sheet_index ) ){
            update_option( $this->sheet_index_key, $this->sheet_index );
            return;
        }elseif( ! empty( $full_index_array ) ){
            update_option( $this->sheet_index_key, $full_index_array );
            return;
        }

        //Set empty value
        update_option( $this->sheet_index_key, [] );
    }

    /**
     * ARRAY WITH PRODUCT_ID AND SHEET INDEX NUMBER (WITHOUT LETER)
     * Already stored index
     * Obviously to be an array
     * 
     * #############
     * IMPORTANT
     * ############# 
     * ACTUALLY when called this method just after get_sheet_row() then 
     * sheet_index will regenerate with new data so with previus data, so no need call from
     * database or from wp_option. 
     * actually by default $this->sheet_index faka thake, tai notun paoa zabe just get_sheet_row() method call korar porei.
     * so if found not empty $this->sheet_index, I will just return.
     * 
     * @author Saiful Islam <codersaiful@gmail.com>
     * @version 1.0.0
     *
     * @return array
     */
    public function get_sheet_index()
    {
        //by default faka thake, jodi pai, mane $this->get_sheet_row() call kora hoyeche, and data ache. so I will jut return it.
        // if( ! empty($this->sheet_index) && is_array( $this->sheet_index ) ) return $this->sheet_index;


        $sheet_index = get_option($this->sheet_index_key, []);
        //Checked array or empty
        if( is_array($sheet_index) && ! empty( $sheet_index ) ){
            $this->sheet_index = $sheet_index;
            return $this->sheet_index;
        }
        return [];
    }

    public function get_sheet_index_by_product_id( $product_id )
    {
        $this->product_id = $product_id;
        $sheet_index = $this->get_sheet_index();
        if( ! empty( $sheet_index[$product_id] ) ){
            return $sheet_index[$product_id];
        }
        return;
    }

    /**
     * Get sheet range for single product from existing list
     * but if not found in existing list, then this method will 
     * return new range for create or add new product in sheet
     * but if fail to pass limit, it will return null.
     *
     * @param int $product_id
     * @return string|null
     */
    public function get_sheet_range_by_product_id( $product_id )
    {
        $this->product_id = $product_id;
        $single_index_number = $this->get_sheet_index_by_product_id( $product_id );
        if( ! empty( $single_index_number ) && is_numeric( $single_index_number ) ){
            $l_letter = $this->get_sheet_last_letter();
            return "A$single_index_number:$l_letter".$single_index_number;
        }
        
        /**
         * When product is not available in already syncronise list
         * then we will return to new range
         * 
         * Actually it's for create new product create.
         * But it would be in limitation of 
         */
        if( is_array( $this->get_sheet_index() ) && count( $this->get_sheet_index() ) <= $this->get_one_load_limit() ){
            $curr = $this->get_sheet_index();
            sort($curr);
            $single_index_number = end($curr) + 1;
            $this->set_sheet_index_by_product_id($product_id, $single_index_number);
            $l_letter = $this->get_sheet_last_letter();
            return "A$single_index_number:$l_letter".$single_index_number;
        }
        return;
    }


    public function get_one_load_limit(){
        $this->one_load_limit = apply_filters( 'pssg_one_load_limit', $this->one_load_limit );
        if( empty( $this->one_load_limit ) || ! is_numeric( $this->one_load_limit ) ){
            return PHP_INT_MAX;
        }
        return $this->one_load_limit;
    }

    /**
     * In sheet's column count, We have to add two extra column
     * because, auto and extra column count is two
     *
     * @return int
     */
    public function get_sheet_column_count()
    {
        return $this->get_columns_count();
        // return 2 + $this->get_columns_count();
    }

    /**
     * When data will upload to sheet, then we need to findout last latter
     *
     * @return void
     */
    public function get_sheet_last_letter(){
        return chr( $this->base_chr + $this->get_sheet_column_count() );
    }

    /**
     * Alias of $this->get_sheet_column_count()
     * 
     * *************************
     * IMPORTANT
     * ****************************
     * NO NEED DIDDRENT for sheet actually
     * we have added ID and Type column at the 
     * $this->get_columns() method
     * 
     * 
     *
     * @return int
     */
    public function get_columns_count()
    {
        return count( $this->get_columns() );
    }

    /**
     * Only indicated in array
     * other or auto added column is not listed here
     *
     * @return array
     */
    public function get_columns()
    {

        $this->columns = [
            'title'     => [
                'type'  => 'normal',
                'title' => __( 'Product Title', 'product-sync-master-sheet' )
            ], 
            'stock'     => [
                'type'  => 'modified',
                'title' => __( 'Stock', 'product-sync-master-sheet' )
            ],            
            '_regular_price'     => [
                'type'  => 'cf',
                'title' => __( 'R Price', 'product-sync-master-sheet' )
            ],
            '_price'     => [
                'type'  => 'cf',
                'title' => __( 'Price', 'product-sync-master-sheet' )
            ],
            '_sale_price'     => [
                'type'  => 'cf',
                'title' => __( 'Sale Price', 'product-sync-master-sheet' )
            ],
        ];

        $requiredCols = [];
        $requiredCols['ID'] = [
            'type'  => 'normal',
            'title' => __('ID','product-sync-master-sheet')
        ];
        $requiredCols['type'] = [
            'type'  => 'normal',
            'title' => __( 'Type', 'product-sync-master-sheet' ),
        ];

        $cols = apply_filters( 'pssg_products_columns', $this->columns );
        if( is_array( $cols ) ){
            return array_merge( $requiredCols, $cols );
        }
        return $requiredCols;
    }

    /**
     * Actually to display Row and setting
     * I have added this feature
     * and ID, type column is compolsury actually
     * 
     * @since 1.0.0.23
     *
     * @return array List of Active column. actuall active colum will handle using filter hook
     */
    public function get_active_columns()
    {
        $all_columns = $this->get_columns();
        $founded_active_columns = apply_filters( 'pssg_products_active_columns', $all_columns );

        //Compolsury Columns
        $active_columns = [];
        $active_columns['ID'] = $all_columns['ID'];
        $active_columns['type'] = $all_columns['type'];

        $final_active_columns = array_merge( $active_columns, $founded_active_columns );


        return $final_active_columns;
    }

    /**
     * This is specially for generate one dimensional array
     * like: 
     * "columns_label": {
        "title": "Product Title",
        "short_description": "Description",
        "sku": "SKU",
        "stock": "Stock",
        "price": "Price",
        "sale_price": "Sale Price",
        "min_quantity": "Min Qty"
    }
     *
     * so that, eta theke sohoje rebel khuje paoa jay
     * @return array Like: ['sku'=> 'SKU', 'title'=> 'Product Title']
     */
    public function get_columns_label()
    {
        $columns_label = [];
        $cols = $this->get_columns();
        foreach( $cols as $key => $col ){
            $columns_label[$key] = ! empty( $col['title'] ) ? $col['title'] : $key;
        }
        return $columns_label;
    }

    public function get_columns_row()
    {
        $generated_cols = [];
        $cols = $this->get_active_columns();
        
        foreach($cols as $col_key => $col ){
            $col_title = $col['title'] ?? $col_key;
            $generated_cols[] = $col_title;
            // $generated_cols[] = $col_key;
        }
        return $generated_cols;
    }

    /**
     * $required property is $product_id;
     * required property for this method is
     * $this->product_data 
     * for this product_data need = $product variable;
     *
     * @return void
     */
    protected function organize_product_element()
    {
        $this->set_product( $this->product_id );
        $elements = [];

        /**
         * To enabel disable some column
         * I have added this feature
         * 
         * Previous:
         * $cols = $this->get_columns();
         * 
         * @since 1.0.0.23
         */
        $cols = $this->get_active_columns();

        foreach($cols as $col_key => $col)
        {
            if( ! is_array( $col )){
                $elements[$col_key] = '';
                continue;
            };

            switch($col_key){
                case 'ID':
                    $elements[] = intval($this->product_id);
                    break;
                case 'type':
                    $elements[] = $this->get_product_type();
                    break;
                case 'title':
                    $elements[] = $this->get_product_title();
                    break;
                case 'short_description':
                    $elements[] = $this->product_data['short_description'] ?? '';
                    break;
                case 'sku':
                    $elements[] = $this->product_data['sku'] ?? '';
                    break;
                case 'stock':
                    $elements[] = $this->get_stock_manage();
                    break;
                case 'price':
                    $elements[] = $this->product_data['price'] ?? '';
                    break;
                
                case 'sale_price':
                    $elements[] = $this->product_data['sale_price'] ?? '';
                    break;
                

                default:
                $elements[] = $this->organize_each_col( $col_key, $col );
                break;
            }

            


        }
        return apply_filters( 'pssg_each_row_data', $elements, $this->product_id );
    }

    /**
     * You have to call this method,
     * obviously after generate 
     * $this->product and
     * $this->product_data
     *
     * @return string|null
     */
    protected function get_stock_manage()
    {
        /**
         * Actually when it will call from single, neeed this.
         * and in that function, already checked that, if foun already product property,
         * wit will not re-genreate actually
         * 
         * @since 1.0.0.16
         */
        $this->set_product( $this->product_id );

        // if( ! is_object( $this->product ) || empty( $this->product ) ) return;
        
        /**
         * I will check by $this->product_type, actually if found product type, 
         * then we will get $this->product and $this->product_data
         * 
         * Only for Variation and Simple product, I will handle Stock from Sheet
         */
        if( empty( $this->get_product_type() ) ) return;
        if( $this->get_product_type() == 'simple' || $this->get_product_type() == 'variation' ){
            $stock = $this->product_data['stock_status'];

            if ( ! $this->product->managing_stock() && 'instock' == $stock){
                return 'In stock';
            }
            if ( ! $this->product->managing_stock() && 'outofstock' == $stock){
                return 'Out of stock';
            }
            return $this->product->get_stock_quantity();
        }
        return '';
    }

    public function organize_each_col( $col_key, $col )
    {
        $this->set_product( $this->product_id );
        $extra_value = '';
        $type = $col['type'] ?? '';
        switch($type){
            case 'cf':
                $extra_value = get_post_meta( $this->product_id, $col_key, true );
                break;
            default:
            $extra_value = apply_filters( 'pssg_each_col_data', '', $col, $this->product_id );
        }

        return $extra_value;
    }


    public function set_paged(  $pageNumber = 1 ){
        if( is_numeric( $pageNumber )){
            $this->paged = $pageNumber;
        }else{
            $this->errors[] = "ErrorCode: Non_Numeric_paged";
        }
        return $this;
    }
    public function set_posts_per_page(  $posts_per_page = 100 ){
        if( is_numeric( $posts_per_page )){
            $this->posts_per_page = $posts_per_page;
        }else{
            $this->errors[] = "ErrorCode: Non_Numeric_posts_per_page";
        }
        return $this;
    }

    /**
     * Get applied args, what already called over here.
     *
     * @return void
     */
    public function get_args()
    {
        return $this->args;
    }
}