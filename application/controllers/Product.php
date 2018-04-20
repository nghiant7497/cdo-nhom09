<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Product extends MY_Controller
{
    function __construct() {
        parent::__construct();

        $this->load->model('product_model');
    }

    /*
     * Hiển thị ds sản phẩm theo danh mục
     */
    function category(){
        //lấy id từ url
        $id = intval($this->uri->rsegment(3));

        //Lấy thông tin của danh mục
        $this->load->model('category_model');
        $category = $this->category_model->get_info($id);

        if(!$category)
            redirect();

        $this->data['categorys'] = $category;

        $input = array();

        //kt xem đây là danh mục cha hay là danh mục con
        if($category->parent_id == 0){//là danh mục cha
            $inp = array();
            $inp['where'] = array('parent_id' => $category->id);
            $category_subs = $this->category_model->get_list();
            //kiểm tra có danh mục con hay không
            if(!empty($category_subs)){//có danh mục con thì lấy product từ list id danh mục con
                $category_subs_id = array();
                foreach ($category_subs as $sub){
                    $category_subs_id[] = $sub->id;
                }
                $this->db->where_in('category_id',$category_subs_id);
            }
            else{//ko có danh mục con thì lấy từ chính id của nó
                $input['where'] = array('category_id' => $category->id);
            }
        }
        else{
            $input['where'] = array('category_id' => $category->id);
        }
        //lấy ds sản phẩm của danh mục
        //lấy tổng sl sp của danh mục
        $total_rows = $this->product_model->get_total($input);
        $this->data['total_rows'] = $total_rows;

        //load thư viện phân trang
        $this->load->library('pagination');
        $config = array();
        $config['total_rows'] = $total_rows;//tong tat ca cac san pham tren website
        $config['base_url']   = base_url('product/category/'.$category->id); //link hien thi ra danh sach san pham
        $config['per_page']   = 6;//so luong san pham hien thi tren 1 trang
        $config['uri_segment'] = 4;//phan doan hien thi ra so trang tren url
        $config['cur_tag_open'] = '<li class="active"><a>';
        $config['cur_tag_close'] = '</a></li>';
        $config['prev_link'] = '< Prev Page';
        $config['prev_tag_open'] = '<li class="prev">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = 'Next Page >';
        $config['next_tag_open'] = '<li class="next">';
        $config['next_tag_close'] = '</li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';

        //khoi tao cac cau hinh phan trang
        $this->pagination->initialize($config);

        $segment = $this->uri->segment(4);
        $segment = intval($segment);
        $input['limit'] = array($config['per_page'], $segment);

        //lay danh sach san pham
        if(isset($category_subs_id))
        {
            $this->db->where_in('category_id', $category_subs_id);
        }
        $products = $this->product_model->get_list($input);
        $this->data['products'] = $products;

        //load view
        $this->data['template'] = 'front/product/category';
        $this->load->view('front/layout', $this->data);
    }
}