<?php

namespace rdx\cronlog;

use db_generic_model;

class Model extends db_generic_model {

	static public function _updates( array $datas ) {
		foreach ( $datas as $id => $data ) {
			if ( static::validate($data) ) {
				if ( $id ) {
					static::find($id)->update($data);
				}
				else {
					static::insert($data);
				}
			}
			else {
				if ( $id ) {
					static::find($id)->delete($data);
				}
			}
		}
	}

	static public function validate( array $data ) {
		return true;
	}

}
