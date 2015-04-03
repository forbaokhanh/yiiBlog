<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{

	// An instance variable
	private $_id;
	// I'm assuming an errorCode variable is inherited from a certain parent class?
	// As well as username

	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
		$username=strtolower($this->username); // Created a username variable and set it to a low String. This is all very case insensetive
		$user=User::model()->find('LOWER(username)=?',array($username)); // Confusing syntax. The ? is going to look for array($username) in the database.
		if ($user==null)
			// Am I correct to assume that every class has ERROR_TYPE static class variables?
			$this->errorCode=self::ERROR_USERNAME_INVALID;  // Setting the errorCode instance variable to point to an Error Object?
		// The validatePasword function is invoked through a user object, which means it has to be in the user class. That's in Models
		else if (!$user->validatePassword($this->password))
			$this->errorCode=self::ERROR_PASSWORD_INVALID;  // What's with the self::ERROR_TYPE notation?
		else
		{
			$this->_id=$user->id;
			$this->username=$user->username;
			$this->errorCode=self::ERROR_NONE;
		}
		return ($this->errorCode == self::ERROR_NONE); // Returning whether there were any problems with authenticating.
	}

	// @Override because the parent implementation would return the username, since it has no idea what an Id is.
	public function getId()
	{
		return $this->_id;
	}
}