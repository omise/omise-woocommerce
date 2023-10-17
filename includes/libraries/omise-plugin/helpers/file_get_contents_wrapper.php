<?php

/**
 * file_get_contents wrapper class
 * 
 * Since we cannot mock global function,
 * we have to create a wrapper for file_get_contents.
 */
class File_Get_Contents_Wrapper
{
	public static function get_contents($url)
	{
		return file_get_contents($url);
	}
}
