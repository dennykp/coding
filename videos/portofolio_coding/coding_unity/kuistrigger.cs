using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class kuistrigger : MonoBehaviour {
	public GameObject oke;

	// Use this for initialization
	private Rigidbody rb;

	string hitName;
	// Use this for initialization
	void Start(){
		rb = GetComponent<Rigidbody> ();
	}
	public void OnTriggerEnter(Collider collider){
		if (collider.gameObject.name == "3") {


			oke.SetActive (true);

		}

	// Use this for initialization

}
	public void OnTriggerExit(Collider col){

		oke.SetActive (false);

	}
}