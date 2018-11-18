<?php
namespace modules\crud_mod_adv;                     //Make sure namespace is same structure with parent directory

use \classes\Auth as Auth;                          //For authentication internal user
use \classes\JSON as JSON;                          //For handling JSON in better way
use \classes\CustomHandlers as CustomHandlers;      //To get default response message
use \classes\Validation as Validation;              //To validate the string
use PDO;                                            //To connect with database

	/**
     * Example to create advanced crud module in reSlim
     *
     * @package    modules/crud_mod_adv
     * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
     * @copyright  Copyright (c) 2018 M ABD AZIZ ALFIAN
     * @license    https://github.com/aalfiann/reSlim-modules-crud_mod_adv/blob/master/LICENSE.md  MIT License
     */
    class CrudModAdv {

        // database var
		protected $db;
		
		//base var
        protected $basepath,$baseurl,$basemod;

        //master var
        var $username,$token;

        //data var
        var $id,$fullname,$address,$telp,$email,$website,$created_at,$created_by,$updated_at,$updated_by,$custom_id,$custom_field;

        //search var
		var $search,$firstdate,$lastdate;
        
        //pagination var
		var $page,$itemsPerPage;
		
		//multi language var
		var $lang;
        
        //construct database object
        function __construct($db=null,$baseurl=null) {
			if (!empty($db)) $this->db = $db;
            $this->baseurl = (($this->isHttps())?'https://':'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
			$this->basepath = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']);
			$this->basemod = dirname(__FILE__);
        }
        
        //Detect scheme host
        function isHttps() {
            $whitelist = array(
                '127.0.0.1',
                '::1'
            );
            
            if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
                if (!empty($_SERVER['HTTP_CF_VISITOR'])){
                    return isset($_SERVER['HTTPS']) ||
                    ($visitor = json_decode($_SERVER['HTTP_CF_VISITOR'])) &&
                    $visitor->scheme == 'https';
                } else {
                    return isset($_SERVER['HTTPS']);
                }
            } else {
                return 0;
            }            
		}
		
		private function searchKeyword($columndb,$text,$options='or',$paramkey=':keywords',$textdelimiter=','){
            $datakey = explode($textdelimiter,$text);
			$listkeys = "";
            $n=0;
            if(!empty($datakey[0])){
                foreach($datakey as $value){
                    if(!empty(trim($value))){ 
                        $listkeys .= 'INSTR('.$columndb.','.$paramkey.$n.') '.trim($options).' ';
                        $n++;
                    }
                }
            }

            $listkeys = rtrim($listkeys," ".trim($options)." ");
            return $listkeys;
        }

        private function selectKeyword($columndb,$text,$options='or',$paramkey=':keywords',$textdelimiter=','){
            $datakey = explode($textdelimiter,$text);
			$listkeys = "";
            $n=0;
            if(!empty($datakey[0])){
                foreach($datakey as $value){
                    if(!empty(trim($value))){ 
                        $listkeys .= $columndb.'='.$paramkey.$n.' '.trim($options).' ';
                        $n++;
                    }
                }
            }

            $listkeys = rtrim($listkeys," ".trim($options)." ");
            return $listkeys;
        }

        private function paramKeyword($columndb,$text,$paramkey=':keywords',$textdelimiter=','){
            $datakey = explode($textdelimiter,$text);
            $listdata = array();
            $n=0;
            if(!empty($datakey[0])){
                foreach($datakey as $value){
                    if(!empty(trim($value))){ 
                        $listdata[$paramkey.$n] = trim($value);
                        $n++;
                    }
                }
            }
            return $listdata;
        }

        //Get modules information
        public function viewInfo(){
            return file_get_contents($this->basemod.'/package.json');
        }

        /**
         * Installation (Build database table) 
         */
        public function install(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
				$role = Auth::getRoleID($this->db,$this->token);
				if ($role == 1){
					try {
						$this->db->beginTransaction();
						$sql = file_get_contents(dirname(__FILE__).'/crud_mod_adv.sql');
						$stmt = $this->db->prepare($sql);
						if ($stmt->execute()) {
							$data = [
								'status' => 'success',
								'code' => 'RS101',
								'message' => CustomHandlers::getreSlimMessage('RS101',$this->lang)
							];	
						} else {
							$data = [
								'status' => 'error',
								'code' => 'RS201',
								'message' => CustomHandlers::getreSlimMessage('RS201',$this->lang)
							];
						}
						$this->db->commit();
					} catch (PDOException $e) {
						$data = [
							'status' => 'error',
							'code' => $e->getCode(),
							'message' => $e->getMessage()
						];
						$this->db->rollBack();
					}
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS404',
						'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
					];
				}
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
            }

			return JSON::encode($data,true);
			$this->db = null;
        }

        /**
         * Uninstall (Remove database table) 
         */
        public function uninstall(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
				$role = Auth::getRoleID($this->db,$this->token);
				if ($role == 1){
					try {
						$this->db->beginTransaction();
						$sql = "DROP TABLE IF EXISTS crud_mod_adv;";
						$stmt = $this->db->prepare($sql);
						if ($stmt->execute()) {
							$data = [
								'status' => 'success',
								'code' => 'RS104',
								'message' => CustomHandlers::getreSlimMessage('RS104',$this->lang)
							];	
						} else {
							$data = [
								'status' => 'error',
								'code' => 'RS204',
								'message' => CustomHandlers::getreSlimMessage('RS204',$this->lang)
							];
						}
						$this->db->commit();
					} catch (PDOException $e) {
						$data = [
							'status' => 'error',
							'code' => $e->getCode(),
							'message' => $e->getMessage()
						];
						$this->db->rollBack();
					}
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS404',
						'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
					];
				}
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
            }

			return JSON::encode($data,true);
			$this->db = null;
        }

        //CRUD===========================================

		public function createData(){
			try {
				$this->db->beginTransaction();
				$sql = "INSERT INTO crud_mod_adv (Fullname,Address,Telp,Email,Website,Custom_id,Custom_field,Created_at,Created_by) 
					VALUES (:fullname,:address,:telp,:email,:website,:custom_id,:custom_field,current_timestamp,:username);";
				$stmt = $this->db->prepare($sql);
				$stmt->bindParam(':fullname', $this->fullname, PDO::PARAM_STR);
				$stmt->bindParam(':address', $this->address, PDO::PARAM_STR);
				$stmt->bindParam(':telp', $this->telp, PDO::PARAM_STR);
				$stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
				$stmt->bindParam(':website', $this->website, PDO::PARAM_STR);
				$stmt->bindParam(':username', $this->username, PDO::PARAM_STR);
				$stmt->bindParam(':custom_id', $this->custom_id, PDO::PARAM_STR);
                $stmt->bindParam(':custom_field', $this->custom_field, PDO::PARAM_STR);
				if ($stmt->execute()) {
					$data = [
						'status' => 'success',
						'code' => 'RS101',
						'message' => CustomHandlers::getreSlimMessage('RS101',$this->lang)
					];	
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS201',
						'message' => CustomHandlers::getreSlimMessage('RS201',$this->lang)
					];
				}
				$this->db->commit();
			} catch (PDOException $e) {
				$data = [
					'status' => 'error',
					'code' => $e->getCode(),
					'message' => $e->getMessage()
				];
				$this->db->rollBack();
			}
			return $data;
			$this->db = null;
		}

		public function updateData(){
			try {
				$this->db->beginTransaction();
				$sql = "UPDATE crud_mod_adv 
					SET Fullname=:fullname,Address=:address,Telp=:telp,Email=:email,Website=:website,Custom_id=:custom_id,Custom_field=:custom_field,
						Updated_at=current_timestamp,Updated_by=:username
					WHERE ID=:id;";
				$stmt = $this->db->prepare($sql);
				$stmt->bindParam(':fullname', $this->fullname, PDO::PARAM_STR);
				$stmt->bindParam(':address', $this->address, PDO::PARAM_STR);
				$stmt->bindParam(':telp', $this->telp, PDO::PARAM_STR);
				$stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
				$stmt->bindParam(':website', $this->website, PDO::PARAM_STR);
				$stmt->bindParam(':username', $this->username, PDO::PARAM_STR);
				$stmt->bindParam(':custom_id', $this->custom_id, PDO::PARAM_STR);
                $stmt->bindParam(':custom_field', $this->custom_field, PDO::PARAM_STR);
				$stmt->bindParam(':id', $this->id, PDO::PARAM_STR);
				if ($stmt->execute()) {
					$data = [
						'status' => 'success',
						'code' => 'RS103',
						'message' => CustomHandlers::getreSlimMessage('RS103',$this->lang)
					];	
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS203',
						'message' => CustomHandlers::getreSlimMessage('RS203',$this->lang)
					];
				}
				$this->db->commit();
			} catch (PDOException $e) {
				$data = [
					'status' => 'error',
					'code' => $e->getCode(),
					'message' => $e->getMessage()
				];
				$this->db->rollBack();
			}
			return $data;
			$this->db = null;
		}

		public function deleteData(){
			try {
				$this->db->beginTransaction();
				$sql = "DELETE FROM crud_mod_adv WHERE ID=:id;";
				$stmt = $this->db->prepare($sql);
				$stmt->bindParam(':id', $this->id, PDO::PARAM_STR);
				if ($stmt->execute()) {
					$data = [
						'status' => 'success',
						'code' => 'RS104',
						'message' => CustomHandlers::getreSlimMessage('RS104',$this->lang)
					];	
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS204',
						'message' => CustomHandlers::getreSlimMessage('RS204',$this->lang)
					];
				}
				$this->db->commit();
			} catch (PDOException $e) {
				$data = [
					'status' => 'error',
					'code' => $e->getCode(),
					'message' => $e->getMessage()
				];
				$this->db->rollBack();
			}
			return $data;
			$this->db = null;
		}

		public function readData(){
			$sql = "SELECT a.ID,a.Fullname,a.Address,a.Telp,a.Email,a.Website,a.Custom_id,a.Custom_field,a.Created_at,a.Created_by,a.Updated_at,a.Updated_by,a.Updated_sys
				FROM crud_mod_adv a
				WHERE a.ID = :id LIMIT 1;";
				
			$stmt = $this->db->prepare($sql);		
			$stmt->bindParam(':id', $this->id, PDO::PARAM_STR);

			if ($stmt->execute()) {	
				if ($stmt->rowCount() > 0){
					$results = JSON::modifyJsonStringInArray($stmt->fetchAll(PDO::FETCH_ASSOC),['Custom_id','Custom_field']);
					$data = [
						'result' => $results, 
						'status' => 'success', 
						'code' => 'RS501',
						'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
					];
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS601',
						'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
					];
				}          	   	
			} else {
				$data = [
					'status' => 'error',
					'code' => 'RS202',
					'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
				];
			}
			return $data;
			$this->db = null;
		}

		public function indexData(){
			$search = "%$this->search%";
			//count total row
			$sqlcountrow = "SELECT count(a.ID) AS TotalRow 
				FROM crud_mod_adv a
				WHERE 
					".(!empty($this->firstdate) && !empty($this->lastdate)?'date(a.Created_at) BETWEEN :firstdate AND :lastdate AND ':'')."
					(a.ID like :search OR a.Fullname like :search)
				ORDER BY a.Created_at DESC;";
			$stmt = $this->db->prepare($sqlcountrow);		
			$stmt->bindParam(':search', $search, PDO::PARAM_STR);
			if (!empty($this->firstdate) && !empty($this->lastdate)){
                $stmt->bindParam(':firstdate', $this->firstdate, PDO::PARAM_STR);
                $stmt->bindParam(':lastdate', $this->lastdate, PDO::PARAM_STR);
            }
				
			if ($stmt->execute()) {	
    			if ($stmt->rowCount() > 0){
					$single = $stmt->fetch();
						
					// Paginate won't work if page and items per page is negative.
					// So make sure that page and items per page is always return minimum zero number.
					$newpage = Validation::integerOnly($this->page);
					$newitemsperpage = Validation::integerOnly($this->itemsPerPage);
					$limits = (((($newpage-1)*$newitemsperpage) <= 0)?0:(($newpage-1)*$newitemsperpage));
					$offsets = (($newitemsperpage <= 0)?0:$newitemsperpage);

					// Query Data
					$sql = "SELECT a.ID,a.Fullname,a.Address,a.Telp,a.Email,a.Website,a.Created_at,a.Created_by,a.Updated_at,a.Updated_by,a.Updated_sys,a.Custom_id,a.Custom_field 
						from crud_mod_adv a
						where 
							".(!empty($this->firstdate) && !empty($this->lastdate)?'date(a.Created_at) BETWEEN :firstdate and :lastdate and ':'')."
							(a.ID like :search or a.Fullname like :search)
						ORDER BY a.Created_at desc LIMIT :limpage , :offpage;";
					$stmt2 = $this->db->prepare($sql);
					$stmt2->bindParam(':search', $search, PDO::PARAM_STR);
					$stmt2->bindValue(':limpage', (INT) $limits, PDO::PARAM_INT);
					$stmt2->bindValue(':offpage', (INT) $offsets, PDO::PARAM_INT);
					if (!empty($this->firstdate) && !empty($this->lastdate)){
						$stmt2->bindParam(':firstdate', $this->firstdate, PDO::PARAM_STR);
						$stmt2->bindParam(':lastdate', $this->lastdate, PDO::PARAM_STR);
					}
						
					if ($stmt2->execute()){
						$pagination = new \classes\Pagination();
						$pagination->totalRow = $single['TotalRow'];
						$pagination->page = $this->page;
						$pagination->itemsPerPage = $this->itemsPerPage;
						$pagination->fetchAllAssoc = JSON::modifyJsonStringInArray($stmt2->fetchAll(PDO::FETCH_ASSOC),['Custom_id','Custom_field']);
						$data = $pagination->toDataArray();
					} else {
						$data = [
        		    		'status' => 'error',
		    		    	'code' => 'RS202',
				    	    'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
						];	
					}			
				} else {
    	    		$data = [
            			'status' => 'error',
	    	    		'code' => 'RS601',
    			    	'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
					];
		    	}          	   	
			} else {
				$data = [
        			'status' => 'error',
					'code' => 'RS202',
	        		'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
				];
			}
			return $data;
		}

		public function indexDataWithKey() {
            $search = "%$this->search%";
            $listkeys = $this->searchKeyword('a.Custom_id',$this->custom_id,'and');
            $listdata = $this->paramKeyword('a.Custom_id',$this->custom_id);
            $listdata[':search'] = $search;
            if (!empty($this->firstdate) && !empty($this->lastdate)){
                $listdata[':firstdate'] = $this->firstdate;
                $listdata[':lastdate'] = $this->lastdate;
            }
			//count total row
			$sqlcountrow = "SELECT count(a.ID) AS TotalRow 
				FROM crud_mod_adv a
				WHERE 
                    ".(!empty($this->firstdate) && !empty($this->lastdate)?'DATE(a.Created_at) BETWEEN :firstdate AND :lastdate AND ':'')."
                    (a.ID LIKE :search OR a.Fullname LIKE :search)
                    ".(!empty($this->custom_id)?' AND '.$listkeys:'')."
				    ORDER BY a.Created_at DESC;";
			$stmt = $this->db->prepare($sqlcountrow);
				
			if ($stmt->execute($listdata)) {	
    			if ($stmt->rowCount() > 0){
					$single = $stmt->fetch();
						
					// Paginate won't work if page and items per page is negative.
					// So make sure that page and items per page is always return minimum zero number.
					$newpage = Validation::integerOnly($this->page);
					$newitemsperpage = Validation::integerOnly($this->itemsPerPage);
					$limits = (((($newpage-1)*$newitemsperpage) <= 0)?0:(($newpage-1)*$newitemsperpage));
                    $offsets = (($newitemsperpage <= 0)?0:$newitemsperpage);
					// Query Data
					$sql = "SELECT 
                            a.ID,a.Fullname,a.Address,a.Telp,a.Email,a.Website,a.Created_at,a.Created_by,a.Updated_at,a.Updated_by,a.Updated_sys,a.Custom_id,a.Custom_field 
                        FROM crud_mod_adv a
                        WHERE 
                            ".(!empty($this->firstdate) && !empty($this->lastdate)?'DATE(a.Created_at) BETWEEN :firstdate AND :lastdate AND ':'')."
                            (a.ID LIKE :search OR a.Fullname LIKE :search)
                            ".(!empty($this->custom_id)?' AND '.$listkeys:'')."
		    				ORDER BY a.Created_at DESC LIMIT ".$limits." , ".$offsets."";
			    		$stmt2 = $this->db->prepare($sql);
					
					if ($stmt2->execute($listdata)){
						$pagination = new \classes\Pagination();
						$pagination->totalRow = $single['TotalRow'];
						$pagination->page = $this->page;
						$pagination->itemsPerPage = $this->itemsPerPage;
						$pagination->fetchAllAssoc = JSON::modifyJsonStringInArray($stmt2->fetchAll(PDO::FETCH_ASSOC),['Custom_id','Custom_field']);
						$data = $pagination->toDataArray();
					} else {
						$data = [
        		    		'status' => 'error',
		    		    	'code' => 'RS202',
				    	    'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
						];	
					}			
				} else {
    	    		$data = [
            			'status' => 'error',
	    	    		'code' => 'RS601',
    			    	'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
					];
		    	}          	   	
			} else {
				$data = [
        			'status' => 'error',
					'code' => 'RS202',
	        		'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
				];
			}	
        
			return $data;
	        $this->db= null;
        }

		//For use in router==============================

        public function create() {
            if (Auth::validToken($this->db,$this->token,$this->username)){
				$roles = Auth::getRoleID($this->db,$this->token);
                if ($roles != '5'){
					$data = $this->createData();
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS404',
						'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
					];
				}
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
            }

			return JSON::encode($data,true);
			$this->db = null;
        }

        public function update() {
            if (Auth::validToken($this->db,$this->token,$this->username)){
				$roles = Auth::getRoleID($this->db,$this->token);
                if ($roles != '5'){
					$data = $this->updateData();
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS404',
						'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
					];
				}
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
					'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang),
					'user' => $this->username
				];
            }

			return JSON::encode($data,true);
			$this->db = null;
        }

        public function delete() {
            if (Auth::validToken($this->db,$this->token,$this->username)){
				$roles = Auth::getRoleID($this->db,$this->token);
                if ($roles == '1'){
					$data = $this->deleteData();
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS404',
						'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
					];
				}
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
            }

			return JSON::encode($data,true);
			$this->db = null;
        }

        public function read() {
            if (Auth::validToken($this->db,$this->token,$this->username)){
				$data = $this->readData();
			} else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}
			
			return JSON::encode($data,true);
	        $this->db= null;
		}
		
		public function readPublic() {
			return JSON::encode($this->readData(),true);
	        $this->db= null;
        }

        public function index() {
            if (Auth::validToken($this->db,$this->token,$this->username)){
				$data = $this->indexData();
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}		
        
			return JSON::safeEncode($data,true);
	        $this->db= null;
		}
		
		public function indexKey() {
            if (Auth::validToken($this->db,$this->token,$this->username)){
				$data = $this->indexDataWithKey();
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}
			return JSON::safeEncode($data,true);
	        $this->db= null;
        }

    }    