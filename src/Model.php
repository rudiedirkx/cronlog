<?php

namespace rdx\cronlog;

use db_generic_model;

class Model extends db_generic_model {

	/**
	 * @param list<AssocArray> $datas
	 * @return void
	 */
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
					static::find($id)->delete();
				}
			}
		}
	}

	/**
	 * @param AssocArray $data
	 */
	static public function validate( array $data ) : bool {
		return true;
	}

}
