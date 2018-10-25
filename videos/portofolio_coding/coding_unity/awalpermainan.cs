using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class awalpermainan : MonoBehaviour {
	public GameObject showuangnama;
	public GameObject panelpetunjuk;
	// Use this for initialization
	void Start () {
		
	}
	
	// Update is called once per frame
	void Update () {
		
	}
	public void petunjuk(){
		showuangnama.SetActive (true);
		panelpetunjuk.SetActive (false);
	}
}
