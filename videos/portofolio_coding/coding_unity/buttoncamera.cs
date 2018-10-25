using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class buttoncamera : MonoBehaviour {
	public GameObject showcamera;
	public GameObject hidecamera;
	public GameObject buttoncamera1;
	public GameObject buttoncamera2;
	// Use this for initialization
	void Start () {
		
	}
	public void show(){
		showcamera.SetActive (true);
		hidecamera.SetActive (false);
		buttoncamera1.SetActive (false);
		buttoncamera2.SetActive (true);
	}
	public void hide(){
		showcamera.SetActive (false);
		hidecamera.SetActive (true);
		buttoncamera1.SetActive (true);
		buttoncamera2.SetActive (false);
	}

	// Update is called once per frame
	void Update () {
		
	}
}
