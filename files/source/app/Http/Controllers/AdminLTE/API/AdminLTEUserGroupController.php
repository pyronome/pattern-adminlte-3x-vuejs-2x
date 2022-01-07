<?php

namespace App\Http\Controllers\AdminLTE\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AdminLTE\AdminLTE;
use App\AdminLTE\AdminLTEModelOption;
use App\AdminLTE\AdminLTEUserGroup;
use App\AdminLTE\AdminLTEUserConfig;
use App\AdminLTE\AdminLTEUserConfigVal;
use App\AdminLTE\AdminLTEUserConfigFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\AdminLTE\AdminLTEPermission;
use App\Http\Requests\AdminLTE\API\AdminLTEUserGroupPOSTRequest;

class AdminLTEUserGroupController extends Controller
{
    public function get(Request $request)
    {    
        $data = [];
        
        $parameters = $request->route()->parameters();

        $id = isset($parameters['id'])
            ? intval($parameters['id'])
            : 0;

        if ($id <= 0) {
            return;
        } // if ($id <= 0) {
        
        // is new ?
        if ('new' == htmlspecialchars($parameters['id'])) {
            return;
        } // if ('new' == htmlspecialchars($parameters['id'])) {

        if ($id > 0) {
            $objectAdminLTEUserGroup = AdminLTEUserGroup::where('id', $id)->where('deleted', 0)->first();
        }
        
        $User = auth()->guard('adminlteuser')->user();

        if ($User->can('viewAny', AdminLTEUserGroup::class) && $User->can('view', $objectAdminLTEUserGroup)) {
            $data['user_can_create'] = $User->can('create', AdminLTEUserGroup::class);
            $data['user_can_read'] = $User->can('view', $objectAdminLTEUserGroup);
            $data['user_can_update'] = $User->can('update', $objectAdminLTEUserGroup);
            $data['user_can_delete'] = $User->can('delete', $objectAdminLTEUserGroup);
            $data['user_can_view'] = $User->can('viewAny', $objectAdminLTEUserGroup);

            $objectAdminLTE = new AdminLTE();
            $displayTexts = $objectAdminLTE->getObjectDisplayTexts('AdminLTEUserGroup', $objectAdminLTEUserGroup);

            $data['id'] = $objectAdminLTEUserGroup->id;
            $data['id__displaytext__'] = $displayTexts['id'];
            $data['deleted'] = $objectAdminLTEUserGroup->deleted;
            $data['deleted__displaytext__'] = $displayTexts['deleted'];
            $data['created_at'] = $objectAdminLTEUserGroup->created_at;
            $data['created_at__displaytext__'] = $displayTexts['created_at'];
            $data['updated_at'] = $objectAdminLTEUserGroup->updated_at;
            $data['updated_at__displaytext__'] = $displayTexts['updated_at'];
            $data['enabled'] = $objectAdminLTEUserGroup->enabled;
            $data['enabled__displaytext__'] = $displayTexts['enabled'];
            $data['admin'] = $objectAdminLTEUserGroup->admin;
            $data['admin__displaytext__'] = $displayTexts['admin'];
            $data['title'] = $objectAdminLTEUserGroup->title;
            $data['title__displaytext__'] = $displayTexts['title'];
            $data['widget_permission'] = $objectAdminLTEUserGroup->widget_permission;
            $data['widget_permission__displaytext__'] = $displayTexts['widget_permission'];
        } // if (null !== $objectAdminLTEUserGroup) {

        return [
            'object' => $data
        ];
    }

    public function post(AdminLTEUserGroupPOSTRequest $request)
    {
        $User = auth()->guard('adminlteuser')->user();
        $has_error = false;
        $error_msg = '';
        $return_data = [];
        
        $id = intval($request->input('id'));

        if ($id > 0) {
            $objectAdminLTEUserGroup = AdminLTEUserGroup::find($id);
            if (!$User->can('update', $objectAdminLTEUserGroup)) {
                $has_error = true;
                $error_msg = __('You can not update this object. Contact your system administrator for more information.');
            }
        } else {
            $objectAdminLTEUserGroup = new AdminLTEUserGroup();
            if (!$User->can('create', AdminLTEUserGroup::class)) {
                $has_error = true;
                $error_msg = __('You can not create any object. Contact your system administrator for more information.');
            }
        } // if ($id > 0) {
        
        if ($has_error) {
            $return_data['id'] = $id;
            $return_data['has_error'] = $has_error;
            $return_data['error_msg'] = $error_msg;

            return $return_data;
        }
        
        $objectAdminLTEUserGroup->deleted = 0;
        $objectAdminLTEUserGroup->enabled = ('' != $request->input('enabled'))
                ? intval($request->input('enabled'))
                : 0; 
        $objectAdminLTEUserGroup->admin = ('' != $request->input('admin'))
                ? intval($request->input('admin'))
                : 0;         
        $objectAdminLTEUserGroup->title = $request->input('title');
        $objectAdminLTEUserGroup->widget_permission = ('' != $request->input('widget_permission'))
                ? intval($request->input('widget_permission'))
                : 0;

        $objectAdminLTEUserGroup->save();
        
        $return_data['id'] = $objectAdminLTEUserGroup->id;
        $return_data['has_error'] = false;
        $return_data['error_msg'] = '';

        return $return_data;
    }

    public function delete(Request $request)
    {
        $User = auth()->guard('adminlteuser')->user();
        $has_error = false;
        $error_msg = '';
        $return_data = [];
        
        $selected_ids = $request->input('selected_ids');
        
        $objects = AdminLTEUserGroup::where('deleted', false)
                ->whereIn('id', $selected_ids)
                ->get();

        foreach ($objects as $object)
        {
            if (!$User->can('delete', $object)) {
                $has_error = true;
                $error_msg = __('You can not delete this object. Contact your system administrator for more information.');
                break;
            }              
        } // foreach ($objects as $object)

        if (!$has_error) {
            foreach ($objects as $object)
            {
                $object->deleted = 1;
                $object->save();                
            } // foreach ($objects as $object)
        }
            
        $return_data['has_error'] = true;
        $return_data['error_msg'] = $error_msg;

        return $return_data;
    }    

    public function get_permission_data(Request $request)
    {    
        $form_data = [];
        
        $parameters = $request->route()->parameters();

        $id = isset($parameters['id'])
            ? intval($parameters['id'])
            : 0;


        if ($id <= 0) {
            return;
        } // if (!isset($parameters['id'])) {

        $objectAdminLTE = new AdminLTE();
        $objectAdminLTEUserGroup = AdminLTEUserGroup::find($id);

        if (null !== $objectAdminLTEUserGroup) {
            $form_data['usergroup_id'] = $id;
            $form_data['title'] = $objectAdminLTEUserGroup->title;
            $form_data['permission_data'] = $objectAdminLTE->getUserGroupPermissions($id);
        }

        return [
            'form_data' => $form_data
        ];
    }

    public function post_permission_data(Request $request)
    {
        $usergroup_id = intval($request->input('usergroup_id'));
        $permission_data_list = $request->input('permission_data');

        $objectAdminLTE = new AdminLTE();

        foreach ($permission_data_list as $permission_data) {
            $meta_key = $permission_data['meta_key'];
            $encodedPermissions = $objectAdminLTE->base64encode(json_encode($permission_data['permissions'], (JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS)));
            $objectPermission = AdminLTEPermission::where('deleted', false)
                ->where('usergroup_id', $usergroup_id)
                ->where('meta_key', $meta_key)
                ->first();

            if (null === $objectPermission) {
                $objectPermission = new AdminLTEPermission();
                $objectPermission->usergroup_id = $usergroup_id;
                $objectPermission->meta_key = $meta_key;
            }
            
            $objectPermission->permissions = $encodedPermissions;
            $objectPermission->save();
        }
        
        return [
            'id' => $usergroup_id
        ];
    }
    
    public function get_layout_data(Request $request) {
        $form_data = [];
        
        $data = [];

        $parameters = $request->route()->parameters();

        $id = isset($parameters['id'])
            ? intval($parameters['id'])
            : 0;

        if ($id <= 0) {
            return;
        } // if (!isset($parameters['id'])) {

        $objectAdminLTEUserGroup = AdminLTEUserGroup::find($id);
        
        if (null !== $objectAdminLTEUserGroup) {
            $form_data['usergroup_id'] = $id;
            $form_data['title'] = $objectAdminLTEUserGroup->title;
            $form_data['selected_pages'] = array();
            $form_data['layout_data'] = '';        

            $objectAdminLTE = new AdminLTE();
            $objectAdminLTEMetas = $objectAdminLTE->getMetaData('__usergroup_layout', $id);

            if (count($objectAdminLTEMetas) > 0) {
                $objectAdminLTEMeta = $objectAdminLTEMetas[0];
                
                $metaData = json_decode(
                    $objectAdminLTE->base64Decode($objectAdminLTEMeta->meta_value),
                    (JSON_HEX_QUOT
                    | JSON_HEX_TAG
                    | JSON_HEX_AMP
                    | JSON_HEX_APOS));

                $iframeData = isset($metaData['iframes']) ? $metaData['iframes'] : [];

                $form_data['selected_pages'] = $iframeData['selected_pages'];
                $form_data['layout_data'] = $iframeData['values'];
            }
        }

        return [
            'form_data' => $form_data
        ];
    }

    public function post_layout_data(Request $request)
    {
        $usergroup_id = intval($request->input('usergroup_id'));
        $metaData = [];
        $metaData['iframes'] = array();
        $metaData['iframes']['selected_pages'] = $request->input('selected_pages');
        $metaData['iframes']['values'] = $request->input('layout_data');
        
        $objectAdminLTE = new AdminLTE();
        $encodedData = $objectAdminLTE->base64encode(json_encode($metaData, (JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS)));
        $success = $objectAdminLTE->setMetaData('__usergroup_layout', $usergroup_id, $encodedData);

        if ($success) {
            $return_data['id'] = $usergroup_id;
            $return_data['has_error'] = false;
            $return_data['error_msg'] = '';
        } else {
            $return_data['id'] = $usergroup_id;
            $return_data['has_error'] = true;
            $return_data['error_msg'] = __('An error occurred while processing your request.');
        }

        return $return_data;
    }

    public function get_config_data(Request $request)
    {
        $objectAdminLTE = new AdminLTE();
        $User = auth()->guard('adminlteuser')->user();

        $parameters = $request->route()->parameters();

        $search_text = '';
        if ($s = \Request::get('s')) {
            $search_text = $s;
        }

        $page = 1;
        if ($p = \Request::get('p')) {
            $page = $p;
        }

        $model_id = 0;
        if ($o = \Request::get('o')) {
            $model_id = $o;
        }

        $sort_variable = 'id';
        $sort_direction = 'asc';

        $limit = 1000;
        $show_pagination = false;

        $current_page = 0;
        $last_page = 0;
        $per_page = 0;
        $from = 0;
        $to = 0;
        $total = 0;
        $next_page_url = null;
        $prev_page_url = null;

        $objectList = AdminLTEUserConfig::defaultQuery($search_text, $sort_variable, $sort_direction)->paginate($limit, ['*'], 'page', $page);

        $current_page = $objectList->currentPage();
        $last_page = $objectList->lastPage();
        $per_page = $objectList->perPage();
        $from = (($current_page - 1) * $per_page) + 1;
        $to = ($current_page * $per_page);
        $total = $objectList->total();
        $next_page_url = ($last_page == $current_page) ? null : 'get_config_data?p=' . ($current_page + 1);
        $prev_page_url = (1 == $current_page) ? null : 'get_config_data?p=' . ($current_page - 1);

        $configList = [];

        foreach ($objectList as $object) {
            if ((1 == $object->enabled) && (($model_id == $object->owner_group) || (1 == $object->system))) {
                $configList[$object->__key]['object'] = $object;
                $configList[$object->__key]['searched'] = false;
            }
        } // foreach ($objectList as $object)

   
        $this->setConfigTree($configList);

        $keys = array_keys($configList);
        
        $basekeyOrders = $this->getBasekeyOrders($configList);
        
        $keyOrders = [];

        foreach ($configList as $key => $data) {
            $object = $data['object'];

            if (!isset($keyOrders[$key])) {
                $orderedKey = '';
                $keyParts = $this->getKeyPartsForOrder($key);
                foreach ($keyParts as $part) {
                    $tempStr = str_replace($part, $basekeyOrders[$part], $part);

                    if ('' != $orderedKey) {
                        $orderedKey .= '.';
                    }

                    $orderedKey .= $tempStr;
                }

                $keyOrders[$key] = $orderedKey;
            }
        }

        natsort($keyOrders);

        $keys = array_keys($keyOrders);
        $list = [];
        $index = 0;

        foreach ($keys as $key) {
            $object = $configList[$key]['object'];

            $metaData = json_decode($object->meta_data_json, (JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP| JSON_HEX_APOS));
            $show_on_group = isset($metaData['show_on_group']) ? intval($metaData['show_on_group']) : 0;
            $show_on_user = isset($metaData['show_on_user']) ? intval($metaData['show_on_user']) : 0;
            $show_on_profile = isset($metaData['show_on_profile']) ? intval($metaData['show_on_profile']) : 0;

            if (1 == $show_on_group) {
                $list[$index] = array();
                $list[$index]['id'] = $object->id;
                $list[$index]['deleted'] = $object->deleted;
                $list[$index]['created_at'] = $object->created_at;
                $list[$index]['updated_at'] = $object->updated_at;
                $list[$index]['owner_group'] = $object->owner_group;
                $list[$index]['enabled'] = $object->enabled;
                $list[$index]['required'] = $object->required;
                $list[$index]['__order'] = $object->__order;
                $list[$index]['type'] = $object->type;
                $list[$index]['parent'] = $this->getParentKey($object->__key);
                $list[$index]['__key'] = $object->__key;
                $list[$index]['title'] = $object->title;
                $list[$index]['default_value'] = $object->default_value;
                $list[$index]['description'] = $object->description;
                $list[$index]['hint'] = $object->hint;
                $list[$index]['option_titles'] = isset($metaData['option_titles']) ? $metaData['option_titles'] : '';
                $list[$index]['option_values'] = isset($metaData['option_values']) ? $metaData['option_values'] : '';
                $list[$index]['toggle_elements'] = isset($metaData['toggle_elements']) ? $metaData['toggle_elements'] : [];
                $list[$index]['url'] = isset($metaData['url']) ? $metaData['url'] : '';
                $list[$index]['content'] = isset($metaData['content']) ? $metaData['content'] : '';
                $list[$index]['min'] = isset($metaData['min']) ? $metaData['min'] : 0;
                $list[$index]['max'] = isset($metaData['max']) ? $metaData['max'] : 0;
                $list[$index]['step'] = isset($metaData['step']) ? $metaData['step'] : 0;
                $list[$index]['multiple'] = isset($metaData['multiple']) ? $metaData['multiple'] : 0;
                $list[$index]['file_types'] = isset($metaData['file_types']) ? $metaData['file_types'] : '';

                $large_screen_size = isset($metaData['large_screen_size']) ? intval($metaData['large_screen_size']) : 12;
                $medium_screen_size = isset($metaData['medium_screen_size']) ? intval($metaData['medium_screen_size']) : 12;
                $small_screen_size = isset($metaData['small_screen_size']) ? intval($metaData['small_screen_size']) : 12;
                $list[$index]['grid_class'] = 
                    ' col-lg-'.$large_screen_size // desktop
                    . ' col-md-'.$medium_screen_size . ' col-sm-'.$medium_screen_size // tablet
                    . ' col-'.$small_screen_size; // mobile

                $list[$index]['level'] = 0;
                if (('group' == $object->type) || ('selection_group' == $object->type)) {
                    $list[$index]['level'] = $this->getGroupLevel($object->__key);
                }

                $list[$index]['min_selection'] = isset($metaData['min_selection']) ? $metaData['min_selection'] : 0;
                $list[$index]['max_selection'] = isset($metaData['max_selection']) ? $metaData['max_selection'] : 0;
                $list[$index]['show_on_group'] = $show_on_group;
                $list[$index]['show_on_user'] = $show_on_user;
                $list[$index]['show_on_profile'] = $show_on_profile;

                $list[$index]['value'] = '';
                if (('group' != $object->type) && ('selection_group' != $object->type)) {
                    $list[$index]['value'] = $this->getConfigVal($object->__key, $model_id);
                }
            }
            
            $index++;
        }
        
        $data = [
            'list' => $list
        ];

        return [
            'search_text' => $search_text,
            'sort_variable' => $sort_variable,
            'sort_direction' => $sort_direction,
            'current_page' => $current_page,
            'last_page' => $last_page,
            'per_page' => $per_page,
            'from' => $from,
            'to' => $to,
            'total' => $total,
            'next_page_url' => $next_page_url,
            'prev_page_url' => $prev_page_url,
            'show_pagination' => $show_pagination,
            'data' => $data
        ];
    }

    public function setConfigTree(&$configList) {
        $need_search = false;
        $list = $configList;
        foreach ($list as $__key => $item) {
            $object = $item['object'];
            //echo 'objectKey:' . $object->__key . '<br>';
            $parentKey = $this->getParentKey($object->__key);
            //echo 'parentKey:' . $parentKey . '<br>';

            if ( ('' != $parentKey) && !isset($configList[$parentKey]) ) {
                $parentObject = $this->getConfigObjectById($object->id);

                if ((null !== $parentObject) && (1 == $parentObject->enabled)) {
                    $configList[$parentKey]['object'] = $parentObject;
                    $configList[$parentKey]['searched'] = false;
                    $need_search = true;
                    $configList[$__key]['searched'] = true;
                } else {
                    // clear unabled sub elements
                    $this->cleanUnabledSubelements($configList, $parentKey);
                }
            }
        }

        if ($need_search) {
            $this->setConfigTree($configList);
        }

        return;
    }

    public function cleanUnabledSubelements(&$configList, $parentKey) {
        foreach ($configList as $__key => $object) {
            if ($this->startsWith($__key,$parentKey)) {
                unset($configList[$__key]);
            }
        }
        return;
    }

    public function getConfigObjectById($id) {
        return AdminLTEUserConfig::where('id', $id)
            /* ->where('owner_group', $owner_group) */
            ->where('deleted', 0)
            /* ->where('enabled', 1) */
            ->first();
    }

    public function getConfigObjectByKey($__key) {
        return AdminLTEUserConfig::where('__key', $__key)
            /* ->where('owner_group', $owner_group) */
            ->where('deleted', 0)
            /* ->where('enabled', 1) */
            ->first();
    }

    public function getParentKey($key) {
        $parent = '';

        if ('' != $key) {
           $parts = explode('.', $key);
           $length = count($parts);
           $base = $parts[$length-1];

           if ($length > 1) {
               $parent = str_replace(('.'.$base), '', $key);
           }
        }

        return $parent;
    }

    public function getKeyPartsForOrder($key) {
        $configuredParts = [];
        $index = 0;
        $parts = explode('.', $key);
        foreach ($parts as $part) {
            if (0 == $index) {
                $configuredParts[$index] = $part;
            } else {
                $configuredParts[$index] = $configuredParts[$index-1] . '.' . $part;
            }

            $index++;
        }

        return $configuredParts;
    }

    public function getBasekeyOrders($configList) {
        $basekeyOrders = [];

        foreach ($configList as $key => $data) {
            $object = $data['object'];
            /* $basekey = $this->getBasekey($key); */

            if (!isset($basekeyOrders[$key])) {
                $basekeyOrders[$key] = $object->__order;
            }
        }

        return $basekeyOrders;
    }

    public function getGroupLevel($__key) {
        $parts = explode('.', $__key);
        $level = count($parts) - 1;
        return $level;
    }

    public function getConfigVal($configKey, $model_id) {
        $val = '';
        $strKey = $configKey . ':' . $model_id . ':' . '0';
        $__key = hash('sha256', $strKey);

        $object = AdminLTEUserConfigVal::where('__key', $__key)
            ->where('deleted', 0)
            ->first();

        if (null !== $object) {
            try {
                $val = Crypt::decryptString($object->value);
            } catch (DecryptException $e) {
                $val = '';
            }
        }

        return $val;
    }

    public function download_file(Request $request) {
        $parameters = $request->route()->parameters();

        $type = isset($parameters['type'])
            ? htmlspecialchars($parameters['type'])
            : '';

        $key = isset($parameters['key'])
            ? htmlspecialchars($parameters['key'])
            : '';

        $current_id = isset($parameters['current_id'])
            ? intval($parameters['current_id'])
            : 0;

        $strKey = $key . ':' . $current_id . ':0';
        $__key = hash('sha256', $strKey);

        if (('' == $type) ||('' == $key)) {
            header('HTTP/1.0 404 Not Found');
            header('Status: 404 Not Found');
            die();
        } // if (0 == $id) {

        if ('default' == $type) {
            $item = AdminLTEUserConfigFile::where('__key', $__key)
            ->where('deleted', 0)
            ->where('file_type', 'default')
            ->first();
        } else {

            $item = AdminLTEUserConfigFile::where('__key', $__key)
            ->where('deleted', 0)
            ->where('file_type', 'uploaded')
            ->first();
        }

        if (is_null($item)) {
            header('HTTP/1.0 404 Not Found');
            header('Status: 404 Not Found');
            die();
        }

        $file_contents = base64_decode($item->file);

        $response = response($item->file)
            ->header('Cache-Control', 'no-cache private')
            ->header('Content-Description', 'File Transfer')
            ->header('Content-Type', $item->mime_type)
            ->header('Content-length', strlen($file_contents))
            ->header('Content-Disposition', 'attachment; filename=' . $item->file_name)
            ->header('Content-Transfer-Encoding', 'binary');

        return [
            'url' => "data:" . $item->mime_type . ";base64," . $response->content(),
            'filename' => $item->file_name
        ];
    }

    public function validateSelectionGroup($key, $val) {
        $result = [
            'has_error' => false,
            'error_msg' => ''
        ];

        $selected_options = [];
        if ('' != $val) {
            $selection_options = explode(',', $val);
        }
        $selectedCount = count($selection_options);

        $selectionGroup = $this->getConfigObjectByKey($key);
        $metaData = json_decode($selectionGroup->meta_data_json, (JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP| JSON_HEX_APOS));
        $min_selection = isset($metaData['min_selection']) ? $metaData['min_selection'] : 0;
        $max_selection = isset($metaData['max_selection']) ? $metaData['max_selection'] : 0;

        if ((0 == $min_selection) && ($max_selection > 0)) {
            if ($selectedCount > $max_selection) {
                $result['has_error'] = true;
                $result['error_msg'] = "You can choose maximum " . $max_selection . " option(s).";
            }
        } else if (($min_selection > 0) && ($max_selection > 0)) {
            if (($selectedCount < $min_selection) && ($selectedCount > $max_selection)) {
                $result['has_error'] = true;
                $result['error_msg'] = "You must choose minimum " . $min_selection . " option(s). You can choose maximum " . $max_selection . " option(s).";
            } else if ($selectedCount < $min_selection) {
                $result['has_error'] = true;
                $result['error_msg'] = "You must choose minimum " . $min_selection . " option(s).";
            } else if ($selectedCount > $max_selection) {
                $result['has_error'] = true;
                $result['error_msg'] = "You can choose maximum " . $max_selection . " option(s).";
            }
        } else if (($min_selection > 0) && (0 == $max_selection)) {
            if ($selectedCount < $min_selection) {
                $result['has_error'] = true;
                $result['error_msg'] = "You must choose minimum " . $min_selection . " option(s).";
            }
        }

        return $result;
    }

    public function validation($request, $config_data) {
        $result = [
            'error_msg' => '',
            'error_count' => 0
        ];

        $errors = [];
        $index = 0;
        
        foreach ($config_data as $__order => $data) {
            $type = $data['type'];
            $key = $data['key'];
            $title = $data['title'];
            $val = isset($data['val']) ? $data['val'] : '';

            if ($data['required']) {
                if ('file' == $type) {
                    if (empty($val)) {
                        $result['error_count']++;
                        $errors[$key] = 'The <b>' . $title . '</b> field is required.';
                    } else if (!$request->hasFile($val)) {
                        $result['error_count']++;
                        $errors[$key] = 'The <b>' . $title . '</b> field is required.';
                    }
                } /* else if ('selection_group' == $type) {
                    if (empty($val)) {
                        $result['error_count']++;
                        $errors[$key] = 'The <b>' . $title . '</b> field is required.';
                    } else {
                        $selectionGroupError = $this->validateSelectionGroup($key, $val);

                        if ($selectionGroupError['has_error']) {
                            $result['error_count']++;
                            $errors[$key] = $selectionGroupError['error_msg'];
                        }
                    }
                } */ else {
                    if (empty($val)) {
                        $result['error_count']++;
                        $errors[$key] = 'The <b>' . $title . '</b> field is required.';
                    }
                }
            }
            
            if (('selection_group' == $type) && ('' != $val)) {
                $selectionGroupError = $this->validateSelectionGroup($key, $val);

                if ($selectionGroupError['has_error']) {
                    $result['error_count']++;
                    $errors[$key] = $selectionGroupError['error_msg'];
                }
            }

            $configObject = $this->getConfigObjectByKey($key);
            $metaData = json_decode($configObject->meta_data_json, (JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP| JSON_HEX_APOS));
            $expression = $metaData['expression'];
            if ('' != $expression) {
                if (0 == preg_match($expression, $val)) {
                    $result['error_count']++;
                    $errors[$key] = $metaData['message'];
                }
            }

        }

        $result['error_msg'] = $errors;

        return $result;
    }

    public function post_config_data(Request $request)
    {
        $User = auth()->guard('adminlteuser')->user();
        $has_error = false;
        $error_msg = '';
        $return_data = [];
        
        $model_id = $request->input('model_id');

        $config_dataJSON = $request->input('config_data');
        $config_data = json_decode(
            $config_dataJSON,
            (JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS)
        );

        $validationResult = $this->validation($request, $config_data);
        if ($validationResult['error_count'] > 0) {
            $return_data['id'] = 1;
            $return_data['has_error'] = true;
            $return_data['error_msg'] = $validationResult['error_msg'];

            return $return_data;
        }

        $files = [];
        $file_index = 0;

        foreach ($config_data as $element_data) {
            if (isset($element_data['key'])) {
                $this->saveConfigParameter($element_data, $model_id);

                if ('file' == $element_data['type']) {
                    $files[$file_index]['parameter'] = $element_data['key'];
                    $files[$file_index]['id'] = $element_data['val'];
                    $files[$file_index]['processtype'] = $request->input($element_data['key'] . 'processtype');
                }
            }
        }

        foreach ($files as $index => $fileData) {
            if ($request->hasFile($fileData['id'])) {
                $file = $request->file($fileData['id']);
                $this->saveFile($model_id, $fileData['parameter'], $file);
            } else {
                $this->updateFile($model_id, $fileData);
            }
        }

        $return_data['id'] = 1;
        $return_data['has_error'] = false;
        $return_data['error_msg'] = '';

        return $return_data;
    }

    public function saveConfigParameter($element_data, $model_id)
    {
        $val = '';
        if (isset($element_data['val'])) {
            $val = $element_data['val'];
        }

        echo 'val eklendi:' . $element_data['key'] . '<br>';
        $strKey = $element_data['key'] . ':' . $model_id . ':0';
        $__key = hash('sha256', $strKey);

        $object = AdminLTEUserConfigVal::where('__key', $__key)
            ->where('deleted', 0)
            ->first();

        if (null !== $object) {
            $object->value = Crypt::encryptString($val);
            $object->save();
        } else {
            $object = new AdminLTEUserConfigVal();
            $object->__key = $__key;
            $object->value = Crypt::encryptString($val);
            $object->save();
        }
    }

    public function saveFile($model_id, $parameter, $file) {
        /* //File Name
        echo $file->getClientOriginalName() . '<br>';
    
        //Display File Extension
        echo $file->getClientOriginalExtension() . '<br>';

        //Display File Real Path
        echo $file->getRealPath() . '<br>';

        //Display File Size
        echo $file->getSize() . '<br>';

        //Display File Mime Type
        echo $file->getMimeType() . '<br>'; */

        $file_name = $file->getClientOriginalName();

        echo 'file eklendi:' . $parameter . '<br>';
        $strKey = $parameter . ':' . $model_id . ':0';
        $__key = hash('sha256', $strKey);

        $object = AdminLTEUserConfigFile::where('deleted', 0)->where('file_type', '!=', 'default')->where('__key', $__key)->first();
        if (null === $object) {
            $object = new AdminLTEUserConfigFile();
        }

        $object->__key = $__key;
        $object->file_name = $file->getClientOriginalName();
        $object->mime_type = $file->getMimeType();
        $object->file_size = $file->getSize();
        $object->file = base64_encode(file_get_contents($file->getRealPath()));
        $object->file_type = 'uploaded';
        $object->save();


        $objConfigVal = AdminLTEUserConfigVal::where('__key', $__key)
            ->where('deleted', 0)
            ->first();

        $objConfigVal->__key = $__key;
        $objConfigVal->value = Crypt::encryptString($file_name);
        $objConfigVal->save();
    }

    public function updateFile($model_id, $file_data) {
        $processtype = $file_data['processtype'];
        if ('' == $processtype) {
            return;
        }

        $strKey = $element_data['key'] . ':' . $model_id . ':0';
        $__key = hash('sha256', $strKey);

        $objConfigVal = AdminLTEUserConfigVal::where('__key', $__key)
            ->where('deleted', 0)
            ->first();

        if (('set_default' == $processtype) || ('removed' == $processtype)) {
            AdminLTEUserConfigFile::where('file_type', 'uploaded')->where('__key', $__key)->delete();

            if (null !== $objConfigVal) {
                $objConfigVal->value = '';
                $objConfigVal->save();
            }
        }
    }

}