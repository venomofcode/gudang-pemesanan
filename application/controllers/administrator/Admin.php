<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Deskripsi_model');
        $this->load->model('Admin_model');
        if ($this->session->userdata('admin_login') != true && $this->session->userdata('super_admin_login') != true && $this->session->userdata('client_login') != true) {
            $url = base_url('');
            redirect($url);
        } elseif ($this->session->userdata('client_login') == true) {
            $url = base_url('client/beranda');
            redirect($url);
        } elseif ($this->session->userdata('admin_login') == true) {
            $url = base_url('admin/beranda');
            redirect($url);
        }
    }

    public function index()
    {
        if ($this->session->userdata('super_admin_login') == true) {
            $id_admin = $this->session->userdata('ses_id');
            $datak = $this->Deskripsi_model->get_login_now($id_admin);
            $qx = $datak->row_array();
            $data['fotok'] = $qx['foto'];
            $data['usernamenow'] = $qx['username'];
            $data['data'] = $this->Admin_model->get_all_admin();
            $data['online'] = $this->Deskripsi_model->get_online();
            $data['nama'] = $this->session->userdata('ses_username');
            $this->load->view('administrator/Admin', $data);
        } else {
            $this->load->view('system/login');
        }
    }

    public function edit_profilku()
    {
        if ($this->session->userdata('super_admin_login') == true) {
            $adm = $this->session->userdata('ses_id');
            $x['data'] = $this->Admin_model->get_profilku($adm);
            $x['online'] = $this->Deskripsi_model->get_online();
            $this->load->view('administrator/Edit_Profilku', $x);
        } else {
            $this->load->view('system/login');
        }
    }

    public function check_username_avalibility()
    {
        if (strlen($_POST['xusername']) < 5) {
            echo '<label class="text-danger"><i class="fa fa-close" style="color:red"></i><b> Username tidak valid / inputan username harus lebih dari 4 karakter</b></span></label>';
        } else {
            $this->load->model('Admin_model');
            if ($this->Admin_model->is_username_available($_POST['xusername'])) {
                echo '<script>$("#simpan").attr("disabled", "disabled");</script>
				<label class="text-danger"><i class="fa fa-close" style="color:red"></i><b> Username  telah terdaftar</b></label>';
            } else {
                echo '<script>document.getElementById("simpan").disabled = false;</script>
				<label class="text-success"><i class="fa fa-check" style="color:green"></i> Username  tersedia</label>';
            }
        }
    }

    public function simpan_admin()
    {
        $config['upload_path'] = './assets/img/avatar/admin/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp';
        $config['encrypt_name'] = true;

        $this->upload->initialize($config);
        if (!empty($_FILES['filefoto']['name'])) {
            if ($this->upload->do_upload('filefoto')) {
                $gbr = $this->upload->data();
                $config['image_library'] = 'gd2';
                $config['source_image'] = './assets/img/avatar/admin/'.$gbr['file_name'];
                $config['create_thumb'] = false;
                $config['maintain_ratio'] = false;
                $config['quality'] = '80%';
                $config['width'] = 350;
                $config['height'] = 350;
                $config['new_image'] = './assets/img/avatar/admin/'.$gbr['file_name'];
                $this->load->library('image_lib', $config);
                $this->image_lib->resize();

                $gambar = $gbr['file_name'];
                $username = strip_tags($this->input->post('xusername'));
                $nama = ucwords(strip_tags(strtolower($this->input->post('xnama'))));
                $password = $this->input->post('xpassword');
                $konfirm_password = $this->input->post('xpassword2');
                $status = $this->input->post('xstatus');
                $creator = $this->session->userdata('ses_username');
                if ($password != $konfirm_password) {
                    echo $this->session->set_flashdata('msg', 'error');
                    redirect('administrator/admin');
                } else {
                    $this->Admin_model->simpan_admin($username, $nama, $password, $status, $gambar, $creator);
                    echo $this->session->set_flashdata('msg', 'success');
                    redirect('administrator/admin');
                }
            } else {
                echo $this->session->set_flashdata('msg', 'warning');
                redirect('administrator/admin');
            }
        } else {
            $username = strip_tags($this->input->post('xusername'));
            $nama = ucwords(strip_tags(strtolower($this->input->post('xnama'))));
            $password = $this->input->post('xpassword');
            $konfirm_password = $this->input->post('xpassword2');
            $status = $this->input->post('xstatus');
            if ($password != $konfirm_password) {
                echo $this->session->set_flashdata('msg', 'error');
                redirect('administrator/admin');
            } else {
                $this->Admin_model->simpan_admin_tanpa_gambar($username, $nama, $password, $status, $creator);
                echo $this->session->set_flashdata('msg', 'success');
                redirect('administrator/admin');
            }
        }
    }

    public function hapus_admin()
    {
        $kode = $this->input->post('kode');
        $datam = $this->Admin_model->get_admin_login($kode);
        $qp = $datam->row_array();
        $p = $qp['foto'];
        $path = base_url().'/assets/img/avatar/admin/'.$p;
        delete_files($path, true);
        $this->Admin_model->hapus_admin($kode);
        echo $this->session->set_flashdata('msg', 'success-hapus');
        redirect('administrator/admin', 'refresh');
    }

    public function update_admin()
    {
        $config['upload_path'] = './assets/img/avatar/admin/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp';
        $config['encrypt_name'] = true;

        $this->upload->initialize($config);
        if (!empty($_FILES['filefoto']['name'])) {
            if ($this->upload->do_upload('filefoto')) {
                $gbr = $this->upload->data();
                $config['image_library'] = 'gd2';
                $config['source_image'] = './assets/img/avatar/admin/'.$gbr['file_name'];
                $config['create_thumb'] = false;
                $config['maintain_ratio'] = false;
                $config['quality'] = '80%';
                $config['width'] = 350;
                $config['height'] = 350;
                $config['new_image'] = './assets/img/avatar/admin/'.$gbr['file_name'];
                $this->load->library('image_lib', $config);
                $this->image_lib->resize();

                $gambar = $gbr['file_name'];
                $kode = $this->input->post('kode');
                $nama = ucwords(strip_tags(strtolower($this->input->post('xnama'))));
                $username = $this->input->post('xusername');
                $password = $this->input->post('xpassword');
                $konfirm_password = $this->input->post('xpassword2');
                $status = $this->input->post('xstatus');
                if (empty($password) && empty($konfirm_password)) {
                    $this->Admin_model->update_admin_tanpa_pass($kode, $nama, $username, $status, $gambar);
                    echo $this->session->set_flashdata('msg', 'info');
                    redirect('administrator/admin');
                } elseif ($password != $konfirm_password) {
                    echo $this->session->set_flashdata('msg', 'error');
                    redirect('administrator/admin');
                } else {
                    $this->Admin_model->update_admin($kode, $nama, $username, $password, $status, $gambar);
                    echo $this->session->set_flashdata('msg', 'info');
                    redirect('administrator/admin');
                }
            } else {
                echo $this->session->set_flashdata('msg', 'warning');
                redirect('administrator/admin');
            }
        } else {
            $kode = $this->input->post('kode');
            $username = strip_tags($this->input->post('xusername'));
            $nama = ucwords(strip_tags(strtolower($this->input->post('xnama'))));
            $password = $this->input->post('xpassword');
            $konfirm_password = $this->input->post('xpassword2');

            $status = $this->input->post('xstatus');
            if (empty($password) && empty($konfirm_password)) {
                $this->Admin_model->update_admin_tanpa_pass_gambar($kode, $nama, $username, $status);
                echo $this->session->set_flashdata('msg', 'info');
                redirect('administrator/admin');
            } elseif ($password != $konfirm_password) {
                echo $this->session->set_flashdata('msg', 'error');
                redirect('administrator/admin');
            } else {
                $this->Admin_model->update_admin_tanpa_gambar($kode, $nama, $username, $password, $status);
                echo $this->session->set_flashdata('msg', 'warning');
                redirect('administrator/admin');
            }
        }
    }

    public function update_profil()
    {
        $config['upload_path'] = './assets/img/avatar/admin/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp';
        $config['encrypt_name'] = true;

        $this->upload->initialize($config);
        if (!empty($_FILES['filefoto']['name'])) {
            if ($this->upload->do_upload('filefoto')) {
                $gbr = $this->upload->data();
                $config['image_library'] = 'gd2';
                $config['source_image'] = './assets/img/avatar/admin/'.$gbr['file_name'];
                $config['create_thumb'] = false;
                $config['maintain_ratio'] = false;
                $config['quality'] = '80%';
                $config['width'] = 350;
                $config['height'] = 350;
                $config['new_image'] = './assets/img/avatar/admin/'.$gbr['file_name'];
                $this->load->library('image_lib', $config);
                $this->image_lib->resize();

                $gambar = $gbr['file_name'];
                $kode = $this->input->post('kode');
                $username = strip_tags($this->input->post('xusername'));
                $nama = ucwords(strip_tags(strtolower($this->input->post('xnama'))));
                $password = $this->input->post('xpassword');
                $konfirm_password = $this->input->post('xpassword2');
                $status = $this->input->post('xstatus');
                if (empty($password) && empty($konfirm_password)) {
                    $this->Admin_model->update_profil_admin_tanpa_pass($kode, $nama, $username, $gambar);
                    echo $this->session->set_flashdata('msg', 'info');
                    redirect('administrator/admin/edit_profilku');
                } elseif ($password != $konfirm_password) {
                    echo $this->session->set_flashdata('msg', 'error');
                    redirect('administrator/admin/edit_profilku');
                } else {
                    $this->Admin_model->update_profil_admin($kode, $nama, $username, $password, $gambar);
                    echo $this->session->set_flashdata('msg', 'info');
                    redirect('administrator/admin/edit_profilku');
                }
            } else {
                echo $this->session->set_flashdata('msg', 'warning');
                redirect('administrator/admin/edit_profilku');
            }
        } else {
            $kode = $this->input->post('kode');
            $username = strip_tags($this->input->post('xusername'));
            $nama = ucwords(strip_tags(strtolower($this->input->post('xnama'))));
            $password = $this->input->post('xpassword');
            $konfirm_password = $this->input->post('xpassword2');
            $status = $this->input->post('xstatus');
            if (empty($password) && empty($konfirm_password)) {
                $this->Admin_model->update_profil_admin_tanpa_pass_gambar($kode, $nama, $username);
                echo $this->session->set_flashdata('msg', 'info');
                redirect('administrator/admin/edit_profilku');
            } elseif ($password != $konfirm_password) {
                echo $this->session->set_flashdata('msg', 'error');
                redirect('administrator/admin/edit_profilku');
            } else {
                $this->Admin_model->update_profil_admin_tanpa_gambar($kode, $nama, $username, $password);
                echo $this->session->set_flashdata('msg', 'warning');
                redirect('administrator/admin/edit_profilku');
            }
        }
    }
}
