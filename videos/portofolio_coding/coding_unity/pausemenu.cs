using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.UI;
using UnityEngine.SceneManagement;
public class pausemenu : MonoBehaviour {

	public GameObject showpanelpause;
	public dadut disableroll;
	public GameObject btnpause;
	public GameObject btnresume;
	public GameObject restartpanel;
	public GameObject panelgomenu;
	public GameObject hideloading;
	public GameObject showslide;
	// Use this for initialization
	void Start () {
		

	}
	
	// Update is called once per frame
	void Update () {
		
	}
	public void gotomenu(){
		panelgomenu.SetActive (true);
	}
	public void yesgomenu(){
		SceneManager.LoadScene ("Edukasimonopoli", LoadSceneMode.Single);


	}
	public void nogomenu(){
		panelgomenu.SetActive (false);
	}
	public void pauseshow(){
		
		Time.timeScale = 0;
		showpanelpause.SetActive (true);
		btnresume.SetActive (true);
		btnpause.SetActive (false);
	}
	public void resumeshow(){

		Time.timeScale = 1;
		showpanelpause.SetActive (false);
		btnresume.SetActive (false);
		btnpause.SetActive (true);
	}

	public void audioslide(){

			showslide.SetActive (true);
	
	
	}
	public void restartyes(){
		Application.LoadLevel ("boardgame");
		restartpanel.SetActive(false);
	}
	public void restartno(){
		restartpanel.SetActive(false);
	}
	public void restartgame(){
		restartpanel.SetActive (true);

	}
	public void quitmenu(){
		Application.LoadLevel("Edukasimonopoli");
	}
}