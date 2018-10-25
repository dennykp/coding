using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class s : MonoBehaviour {
	public float speed;
	// Use this for initialization
	void Update(){
		if(Input.GetKey(KeyCode.RightArrow)){
			transform.Translate( speed *Time.deltaTime,0,0);

		}
		if(Input.GetKey(KeyCode.LeftArrow)){
			transform.Translate(speed *Time.deltaTime, 0,0);
		}
		if(Input.GetKey(KeyCode.UpArrow)){
			transform.Translate(speed*Time.deltaTime,0,0);
		}
		if(Input.GetKey(KeyCode.DownArrow)){
			transform.Translate(speed*Time.deltaTime,0,0);
		}
	}
}
